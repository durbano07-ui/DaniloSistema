<?php

namespace App\Http\Controllers;

use App\Models\OrdenSalida;
use App\Models\Producto;
use App\Support\DespachoService;
use App\Support\FormatoActa;
use Dompdf\Dompdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Salida de equipos con factura: genera el Acta de Entrega con precios e IVA.
 */
class ActaController extends Controller
{
    /**
     * Formulario de nueva salida con factura.
     */
    public function create(): View
    {
        $this->authorize('gestionar-inventario');

        // Solo se cargan los equipos que ya estaban en el acta (al volver tras un error de validación)
        $seriesPrevias = collect(old('items', []))->pluck('numero_serie')->filter()->all();
        $productosPrevios = Producto::with('categoria')
            ->whereIn('numero_serie', $seriesPrevias)
            ->where('estado', 'disponible')
            ->get()
            ->map(fn ($p) => self::datosProducto($p));

        $formato = FormatoActa::get();
        $siguienteFactura = self::siguienteFactura();

        return view('consumos.factura', compact('productosPrevios', 'formato', 'siguienteFactura'));
    }

    /**
     * Busca equipos disponibles por nombre o serie mientras se escribe o escanea.
     */
    public function buscar(Request $request): JsonResponse
    {
        $this->authorize('gestionar-inventario');

        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $productos = Producto::with('categoria')
            ->where('estado', 'disponible')
            ->whereNotIn('numero_serie', (array) $request->query('excluir', []))
            ->where(fn ($query) => $query
                ->where('numero_serie', 'like', "%{$q}%")
                ->orWhere('nombre', 'like', "%{$q}%"))
            // La serie exacta (lectura del escáner) aparece primero
            ->orderByRaw('numero_serie = ? desc', [$q])
            ->orderBy('nombre')
            ->limit(10)
            ->get()
            ->map(fn ($p) => self::datosProducto($p));

        return response()->json($productos);
    }

    private static function datosProducto(Producto $p): array
    {
        return [
            'numero_serie' => $p->numero_serie,
            'nombre' => $p->nombre,
            'categoria' => $p->categoria?->nombre,
            'tipo' => $p->tipo ?? 'unitario',
            'disponible' => ($p->tipo ?? 'unitario') === 'lote' ? (int) $p->cantidad_disponible : 1,
            'unidad' => $p->esMetros() ? 'metros' : 'unidades',
        ];
    }

    /**
     * Registra la salida con factura y descuenta los equipos del inventario.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('gestionar-inventario');

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'nota' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.numero_serie' => ['required', 'string', 'distinct', 'exists:productos,numero_serie'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Agrega al menos un equipo al acta.',
            'items.*.precio_unitario.required' => 'Ingresa el precio unitario de cada equipo.',
        ]);

        $formato = FormatoActa::get();

        [$orden] = DespachoService::registrar(
            [
                'tipo' => 'factura',
                'fecha' => $validated['fecha'],
                'motivo' => 'Acta de entrega — '.$formato['destino'],
                'factura' => self::siguienteFactura(),
                'nota' => $validated['nota'] ?? null,
                'iva' => $formato['iva'],
                'datos_acta' => collect($formato)->except('iva')->all(),
            ],
            $validated['items'],
            $request->user()->numero_cedula,
            $request->user()->numero_cedula,
        );

        return redirect()->route('consumos.acta', $orden)
            ->with('status', 'Salida con factura registrada. Se generó el acta de entrega.');
    }

    /**
     * Muestra el Acta de Entrega de una orden con factura.
     */
    public function show(Request $request, OrdenSalida $orden): View
    {
        $this->verificarAcceso($request, $orden);

        return view('consumos.acta', $this->datosActa($orden));
    }

    /**
     * Descarga el Acta de Entrega en PDF.
     */
    public function pdf(Request $request, OrdenSalida $orden): StreamedResponse
    {
        $this->verificarAcceso($request, $orden);

        $html = view('consumos.acta-pdf', $this->datosActa($orden))->render();

        $dompdf = new Dompdf(['defaultFont' => 'DejaVu Sans', 'chroot' => public_path()]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombre = 'Acta-ORD-'.str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT).'.pdf';

        return response()->streamDownload(fn () => print ($dompdf->output()), $nombre, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Formulario para editar los datos fijos del acta (Exclusivo Administrador).
     */
    public function editFormato(): View
    {
        $this->authorize('editar-formato-reporte');

        $formato = FormatoActa::get();

        return view('consumos.formato-acta', compact('formato'));
    }

    /**
     * Actualiza los datos fijos del acta.
     */
    public function updateFormato(Request $request): RedirectResponse
    {
        $this->authorize('editar-formato-reporte');

        $reglas = collect(FormatoActa::CAMPOS)->map(fn () => ['required', 'string', 'max:150'])->all();
        $reglas['iva'] = ['required', 'numeric', 'min:0', 'max:100'];

        $validated = $request->validate($reglas);
        // El IVA se ingresa como porcentaje (15) y se guarda como fracción (0.15)
        $validated['iva'] = round($validated['iva'] / 100, 4);

        FormatoActa::guardar($validated);

        return redirect()->route('consumos.index')
            ->with('status', 'Formato del acta de entrega actualizado.');
    }

    /**
     * Número de factura correlativo siguiente (formato 001, 002, ...).
     */
    private static function siguienteFactura(): string
    {
        $ultima = OrdenSalida::where('tipo', 'factura')
            ->whereNotNull('factura')
            ->latest('id_orden')
            ->value('factura');

        $secuencial = $ultima ? ((int) preg_replace('/\D/', '', substr(strrchr('-'.$ultima, '-'), 1))) + 1 : 1;

        return sprintf('%03d', $secuencial);
    }

    private function verificarAcceso(Request $request, OrdenSalida $orden): void
    {
        abort_unless($orden->esFactura(), 404);

        // Un Técnico solo puede ver las actas de sus propias órdenes
        if ($request->user()->tieneRol('Tecnico') && $orden->numero_cedula !== $request->user()->numero_cedula) {
            abort(403);
        }
    }

    private function datosActa(OrdenSalida $orden): array
    {
        $orden->load(['consumos.producto', 'usuario']);

        $datos = array_merge(FormatoActa::defaults(), $orden->datos_acta ?? []);
        $iva = (float) ($orden->iva ?? 0);

        $lineas = $orden->consumos->map(fn ($c) => [
            'descripcion' => ($c->producto?->nombre ?? 'Equipo').' — S/N: '.$c->numero_serie,
            'cantidad' => (int) ($c->cantidad ?? 1),
            'unidad' => $c->producto?->esMetros() ? 'metros' : 'unidades',
            'precio' => (float) ($c->precio_unitario ?? 0),
            'total' => round((int) ($c->cantidad ?? 1) * (float) ($c->precio_unitario ?? 0), 2),
        ]);

        $subtotal = round($lineas->sum('total'), 2);
        $montoIva = round($subtotal * $iva, 2);

        return [
            'orden' => $orden,
            'datos' => $datos,
            'lineas' => $lineas,
            'iva' => $iva,
            'subtotal' => $subtotal,
            'montoIva' => $montoIva,
            'total' => $subtotal + $montoIva,
        ];
    }
}
