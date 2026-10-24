<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Consumo;
use App\Models\Producto;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Producto::with(['categoria', 'marca', 'modelo', 'almacen', 'proveedor', 'usuario']);

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_serie', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('id_cat', $request->categoria);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $productos = $query->orderBy('nombre')->paginate(10)->withQueryString();
        $categorias = Categoria::all();

        $stats = [
            'total' => Producto::count(),
            'unitarios' => Producto::where('tipo', 'unitario')->count(),
            'lotes' => Producto::where('tipo', 'lote')->count(),
            'unidades_lote_disponibles' => (int) Producto::where('tipo', 'lote')->where('estado', 'disponible')->sum('cantidad_disponible'),
        ];

        return view('productos.index', compact('productos', 'categorias', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categorias = Categoria::with(['marcas.modelos'])->orderBy('nombre')->get();
        $almacenes = Almacen::all();
        $proveedores = Proveedor::all();

        return view('productos.create', compact('categorias', 'almacenes', 'proveedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['numero_cedula'] = $request->user()->numero_cedula;
        $data['precio'] = $data['precio'] ?? 0;
        $data['estado'] = $data['estado'] ?? 'disponible';
        $data['tipo'] = $data['tipo'] ?? 'unitario';
        if ($data['tipo'] === 'lote') {
            $cant = max(1, (int) ($data['cantidad'] ?? 1));
            $data['cantidad'] = $cant;
            $data['cantidad_disponible'] = $cant;
            $data['unidad'] = $data['unidad'] ?? 'unidades';
        } else {
            $data['cantidad'] = 1;
            $data['cantidad_disponible'] = 1;
            $data['unidad'] = 'unidades';
        }

        Producto::create($data);

        return redirect()->route('productos.index')->with('status', ($data['tipo'] === 'lote' ? 'Lote de '.Producto::formatearCantidad($data['cantidad'], $data['unidad']).' registrado exitosamente en el inventario.' : 'Producto registrado exitosamente en el inventario.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto): View
    {
        $producto->load(['marca', 'modelo']);
        $categorias = Categoria::with(['marcas.modelos'])->orderBy('nombre')->get();
        $almacenes = Almacen::all();
        $proveedores = Proveedor::all();

        return view('productos.edit', compact('producto', 'categorias', 'almacenes', 'proveedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $data = $request->validated();
        if (! isset($data['precio'])) {
            $data['precio'] = $producto->precio ?? 0;
        }

        $producto->update($data);

        return redirect()->route('productos.index')->with('status', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->consumos()->count() > 0) {
            return redirect()->route('productos.index')->with('error', 'No se puede eliminar el producto porque tiene consumos técnicos registrados.');
        }

        $producto->delete();

        return redirect()->route('productos.index')->with('status', 'Producto eliminado exitosamente.');
    }

    /**
     * API / AJAX: Escanear código de barras para verificación rápida y salidas (CU-05).
     */
    public function scanBarcode(Request $request, string $serie): JsonResponse
    {
        $serie = trim($serie);
        $producto = Producto::with(['categoria', 'marca', 'modelo', 'almacen.direccion.ciudad', 'proveedor'])
            ->where('numero_serie', $serie)
            ->first();

        if (! $producto) {
            return response()->json([
                'encontrado' => false,
                'mensaje' => "El código o serie '{$serie}' no está registrado en el inventario.",
            ], 404);
        }

        $ultimoConsumo = Consumo::with('usuario')
            ->where('numero_serie', $serie)
            ->latest('id_consumo')
            ->first();

        return response()->json([
            'encontrado' => true,
            'es_disponible' => ($producto->estado === 'disponible'),
            'producto' => [
                'numero_serie' => $producto->numero_serie,
                'nombre' => $producto->nombre,
                'estado' => $producto->estado,
                'tipo' => $producto->tipo ?? 'unitario',
                'cantidad' => (int) ($producto->cantidad ?? 1),
                'cantidad_disponible' => (int) ($producto->cantidad_disponible ?? 1),
                'unidad' => $producto->unidad ?? 'unidades',
                'precio' => number_format((float) $producto->precio, 2),
                'categoria' => $producto->categoria?->nombre ?? 'Sin categoría',
                'id_cat' => $producto->id_cat,
                'marca' => $producto->marca?->nombre,
                'modelo' => $producto->modelo?->nombre,
                'almacen' => $producto->almacen?->nombre ?? 'Sin almacén',
                'id_almacen' => $producto->id_almacen,
                'ciudad' => $producto->almacen?->direccion?->ciudad?->nombre ?? 'N/A',
                'proveedor' => $producto->proveedor?->nombre ?? 'N/A',
            ],
            'ultimo_consumo' => $ultimoConsumo ? [
                'id_consumo' => $ultimoConsumo->id_consumo,
                'fecha' => Carbon::parse($ultimoConsumo->fecha)->format('d/m/Y'),
                'motivo' => $ultimoConsumo->motivo,
                'tecnico' => $ultimoConsumo->usuario?->nombre,
                'cedula' => $ultimoConsumo->numero_cedula,
            ] : null,
        ]);
    }

    /**
     * API / AJAX: Verificar si una serie ya existe antes de ingresar un producto nuevo (Entrada).
     */
    public function checkSerie(Request $request, string $serie): JsonResponse
    {
        $serie = trim($serie);
        $producto = Producto::where('numero_serie', $serie)->first();

        if ($producto) {
            return response()->json([
                'existe' => true,
                'mensaje' => "La serie '{$serie}' ya está registrada con el producto '{$producto->nombre}' (Estado: {$producto->estado}).",
                'producto' => [
                    'numero_serie' => $producto->numero_serie,
                    'nombre' => $producto->nombre,
                    'estado' => $producto->estado,
                ],
            ]);
        }

        return response()->json([
            'existe' => false,
            'mensaje' => 'Serie libre y lista para registrar.',
        ]);
    }
}
