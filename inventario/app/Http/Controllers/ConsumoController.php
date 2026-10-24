<?php

namespace App\Http\Controllers;

use App\Models\Consumo;
use App\Models\OrdenSalida;
use App\Models\Reporte;
use App\Support\DespachoService;
use App\Models\Producto;
use App\Models\Usuario;
use App\Http\Requests\StoreConsumoRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ConsumoController extends Controller
{
    /**
     * Muestra el historial de consumos y retiros técnicos.
     */
    public function index(Request $request): View
    {
        // Por defecto se muestran las órdenes de salida del día actual
        $fecha = $request->input('fecha', now()->toDateString());

        $query = OrdenSalida::with(['usuario', 'consumos.producto.categoria', 'consumos.notas.usuario'])
            ->withCount('consumos')
            ->withSum('consumos', 'cantidad')
            ->whereDate('fecha', $fecha)
            ->latest('id_orden');

        // Si es Técnico, filtra sus propias órdenes; el Almacenista y Administrador pueden ver todas
        if ($request->user()?->tieneRol('Tecnico')) {
            $query->where('numero_cedula', $request->user()->numero_cedula);
        }

        $ordenes = $query->paginate(15)->withQueryString();

        return view('consumos.index', compact('ordenes', 'fecha'));
    }

    /**
     * Muestra el formulario para registrar un nuevo retiro o consumo técnico.
     */
    public function create(Request $request): View
    {
        $productosDisponibles = Producto::with(['categoria', 'marca', 'modelo', 'almacen', 'proveedor'])
            ->where('estado', 'disponible')
            ->orderBy('nombre')
            ->get();

        $tecnicos = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'Tecnico');
        })->where('estado', true)->orderBy('nombre')->get();

        $initialSerie = $request->query('serie', old('numero_serie', ''));

        return view('consumos.create', compact('productosDisponibles', 'tecnicos', 'initialSerie'));
    }

    /**
     * Registra el consumo técnico y actualiza el estado del producto a 'consumido'.
     * Soporta tanto salidas individuales como pedidos de múltiples productos acumulados en tabla.
     */
    public function store(StoreConsumoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Normalizar a una lista de items
        $items = [];
        if (!empty($validated['items']) && is_array($validated['items'])) {
            $items = $validated['items'];
        } elseif (!empty($validated['numero_serie'])) {
            $items[] = [
                'numero_serie' => $validated['numero_serie'],
                'cantidad' => max(1, (int) ($validated['cantidad'] ?? 1)),
            ];
        }

        if (empty($items)) {
            return back()->withErrors(['items' => 'Debes agregar al menos un equipo a la salida.'])->withInput();
        }

        // Si es Técnico se auto-asigna; si es Almacenista/Admin y especificó cédula de técnico, se asigna a ese técnico
        $cedulaResponsable = ($request->user()->tieneRol('Tecnico'))
            ? $request->user()->numero_cedula
            : ($validated['numero_cedula'] ?? $request->user()->numero_cedula);

        [, $consumosCreados] = DespachoService::registrar(
            ['tipo' => 'normal', 'fecha' => $validated['fecha'], 'motivo' => $validated['motivo']],
            $items,
            $cedulaResponsable,
            $request->user()->numero_cedula,
        );

        if (count($consumosCreados) === 1) {
            $consumo = $consumosCreados[0];
            $detalleCant = ($consumo->cantidad > 1) ? "({$consumo->cantidad} unidades)" : "";
            return redirect()->route('consumos.show', $consumo)
                ->with('status', "¡Salida registrada exitosamente! Se generó el comprobante #CNS-" . str_pad($consumo->id_consumo, 4, '0', STR_PAD_LEFT) . " para {$consumo->numero_serie} {$detalleCant}.");
        }

        $totalItems = count($consumosCreados);
        $totalUnidades = array_sum(array_map(fn($c) => $c->cantidad, $consumosCreados));
        return redirect()->route('consumos.index', ['fecha' => $validated['fecha']])
            ->with('status', "¡Salida de pedido registrada exitosamente! Se procesaron {$totalItems} equipos en la orden ({$totalUnidades} unidades despachadas).");
    }

    /**
     * Muestra la nota/comprobante de consumo técnico.
     */
    public function show(Consumo $consumo): View
    {
        $consumo->load(['producto.categoria', 'producto.almacen', 'usuario.rol']);

        return view('consumos.show', compact('consumo'));
    }

    /**
     * Muestra el comprobante de una orden de salida con todos sus equipos.
     */
    public function orden(Request $request, OrdenSalida $orden): View|RedirectResponse
    {
        if ($orden->esFactura()) {
            return redirect()->route('consumos.acta', $orden);
        }

        // Un Técnico solo puede ver los comprobantes de sus propias órdenes
        if ($request->user()->tieneRol('Tecnico') && $orden->numero_cedula !== $request->user()->numero_cedula) {
            abort(403);
        }

        $orden->load(['usuario', 'consumos.producto.categoria', 'consumos.producto.almacen']);

        return view('consumos.orden', compact('orden'));
    }

    /**
     * Anula o revierte un consumo en caso de error, regresando el producto a 'disponible'.
     */
    public function destroy(Consumo $consumo): RedirectResponse
    {
        $this->authorize('registrar-consumo');

        DB::transaction(function () use ($consumo) {
            $producto = $consumo->producto;
            if ($producto) {
                if ($producto->tipo === 'lote') {
                    $cantRevertir = (int) ($consumo->cantidad ?? 1);
                    $producto->update([
                        'cantidad_disponible' => $producto->cantidad_disponible + $cantRevertir,
                        'estado' => 'disponible',
                    ]);
                } elseif ($producto->estado === 'consumido') {
                    $producto->update(['estado' => 'disponible']);
                }
            }

            // Quita el equipo de los reportes generados; si un reporte queda vacío, se elimina
            $reportes = $consumo->reportes()->get();
            $consumo->reportes()->detach();
            foreach ($reportes as $reporte) {
                if (!DB::table('reportables')->where('id_reporte', $reporte->id_reporte)->exists()) {
                    $reporte->delete();
                }
            }

            $orden = $consumo->orden;
            $consumo->delete();

            // Si la orden queda sin equipos, se elimina
            if ($orden && !$orden->consumos()->exists()) {
                $orden->delete();
            }
        });

        return redirect()->route('consumos.index', ['fecha' => \Carbon\Carbon::parse($consumo->fecha)->toDateString()])
            ->with('status', "Consumo anulado. El stock de {$consumo->numero_serie} ha sido restaurado en bodega.");
    }
}
