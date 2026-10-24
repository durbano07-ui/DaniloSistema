<?php

namespace App\Support;

use App\Models\Consumo;
use App\Models\Producto;
use App\Models\Reporte;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Arma el contenido tabular de un reporte para exportarlo a Excel o PDF.
 */
class ReporteExport
{
    public function __construct(public Reporte $reporte)
    {
        $this->reporte->load([
            'usuario.rol',
            'productos.categoria',
            'productos.modelo',
            'almacenes.productos.categoria',
            'almacenes.productos.modelo',
            'usuarios.rol',
            'consumos.producto.almacen',
            'consumos.orden',
            'consumos.usuario',
        ]);
    }

    public function codigo(): string
    {
        return 'REP-'.str_pad($this->reporte->id_reporte, 4, '0', STR_PAD_LEFT);
    }

    public function nombreArchivo(string $extension): string
    {
        return $this->codigo().'-'.str($this->reporte->tipo)->slug().'.'.$extension;
    }

    public const COLUMNAS_ACTA_SALIDAS = ['No.', 'Fecha', 'Descripción', 'Cant.', 'Observaciones', 'Notas', 'Quien recibe', 'Firma', 'Quien entrega', 'Firma'];

    /**
     * Encabezado fijo del formato "Acta de Entrega" (GRH-FO-16).
     */
    public const ENCABEZADO_ACTA_SALIDAS = [
        'codigo' => 'GRH-FO-16',
        'version' => '',
        'fecha' => '2026-10-05',
    ];

    /**
     * Filas del Acta de Entrega: cada equipo de las salidas normales del rango de fechas.
     *
     * @return Collection<int, array>
     */
    public static function actaSalidas(string $desde, string $hasta): Collection
    {
        return Consumo::with(['producto', 'usuario', 'reportes.usuario', 'notas'])
            ->whereHas('orden', fn ($q) => $q->where('tipo', 'normal'))
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->orderBy('fecha')
            ->orderBy('id_consumo')
            ->get()
            ->values()
            ->map(fn ($c, $i) => [
                $i + 1,
                Carbon::parse($c->fecha)->format('d/m/Y'),
                self::nombreConUnidad($c->producto),
                (int) ($c->cantidad ?? 1),
                $c->motivo,
                // Notas de seguimiento, de la más antigua a la más reciente
                $c->notas->sortBy('id_nota')
                    ->map(fn ($n) => $n->created_at->format('d/m/Y').': '.$n->texto)
                    ->implode("\n"),
                $c->usuario?->nombre ?? $c->numero_cedula,
                '',
                $c->reportes->first()?->usuario?->nombre ?? '',
                '',
            ]);
    }

    /**
     * Nombre del producto indicando "(m)" cuando el lote se mide en metros.
     */
    public static function nombreConUnidad(?Producto $producto): string
    {
        if (! $producto) {
            return 'Equipo';
        }

        return $producto->esMetros() ? "{$producto->nombre} (m)" : $producto->nombre;
    }

    public const COLUMNAS_EXISTENCIAS = ['Descripción / Equipo', 'Categoría', 'Marca', 'Modelo', 'Ubicación', 'Stock en Bodega'];

    /**
     * Stock disponible actual agrupado por equipo, categoría, marca, modelo y bodega.
     *
     * @return Collection<int, array{0: string, 1: string, 2: string, 3: string, 4: string, 5: int}>
     */
    public static function existenciasActuales(): Collection
    {
        return Producto::with(['categoria', 'marca', 'modelo', 'almacen'])
            ->where('estado', 'disponible')
            ->get()
            ->groupBy(fn ($p) => implode('|', [$p->nombre, $p->id_cat, $p->id_marca, $p->id_modelo, $p->id_almacen]))
            ->map(function ($grupo) {
                $p = $grupo->first();

                return [
                    self::nombreConUnidad($p),
                    $p->categoria?->nombre ?? '—',
                    $p->marca?->nombre ?? '—',
                    $p->modelo?->nombre ?? '—',
                    $p->almacen?->nombre ?? '—',
                    $grupo->sum(fn ($prod) => ($prod->tipo ?? 'unitario') === 'lote' ? (int) $prod->cantidad_disponible : 1),
                ];
            })
            ->filter(fn ($fila) => $fila[5] > 0)
            ->sortBy([[4, 'asc'], [0, 'asc']])
            ->values();
    }

    /**
     * Agrupa los artículos por producto, modelo y categoría sumando el stock disponible.
     */
    public static function existencias(Collection $productos): Collection
    {
        return $productos
            ->groupBy(fn ($prod) => $prod->nombre.'|'.$prod->id_modelo.'|'.$prod->id_cat)
            ->map(fn ($grupo) => [
                'nombre' => self::nombreConUnidad($grupo->first()),
                'modelo' => $grupo->first()->modelo?->nombre,
                'categoria' => $grupo->first()->categoria?->nombre,
                'stock' => $grupo->sum(fn ($prod) => $prod->estado !== 'disponible'
                    ? 0
                    : (($prod->tipo ?? 'unitario') === 'lote' ? (int) $prod->cantidad_disponible : 1)),
            ])
            ->sortBy('nombre')
            ->values();
    }

    /**
     * Secciones del reporte: cada una con título opcional, encabezados, filas y total.
     *
     * @return array<int, array{titulo: ?string, encabezados: array, filas: array, total: ?array}>
     */
    public function secciones(): array
    {
        $r = $this->reporte;

        return match ($r->tipo) {
            'Inventario General' => [$this->seccionExistencias(null, $r->productos)],
            'Existencias por Bodega' => $r->almacenes
                ->map(fn ($alm) => $this->seccionExistencias($alm->nombre, $alm->productos))
                ->all(),
            'Salidas y Despachos' => [[
                'titulo' => null,
                'encabezados' => ['Vale', 'Fecha', 'Equipo', 'Serie', 'Técnico', 'Cédula', 'Bodega', 'Motivo'],
                'filas' => $r->consumos->map(fn ($c) => [
                    'CNS-'.str_pad($c->id_consumo, 4, '0', STR_PAD_LEFT),
                    Carbon::parse($c->fecha)->format('d/m/Y'),
                    self::nombreConUnidad($c->producto),
                    $c->numero_serie,
                    $c->usuario?->nombre ?? 'N/A',
                    $c->numero_cedula,
                    $c->producto?->almacen?->nombre ?? 'Central',
                    $c->motivo,
                ])->all(),
                'total' => ['Total equipos despachados', $r->consumos->count()],
            ]],
            'Salidas con Factura' => [[
                'titulo' => null,
                'encabezados' => ['Factura', 'Fecha', 'Equipo', 'Serie', 'Cantidad', 'Precio U.', 'Precio Total'],
                'filas' => $r->consumos->map(fn ($c) => [
                    $c->orden?->factura ?? '—',
                    Carbon::parse($c->fecha)->format('d/m/Y'),
                    self::nombreConUnidad($c->producto),
                    $c->numero_serie,
                    (int) ($c->cantidad ?? 1),
                    round((float) $c->precio_unitario, 2),
                    round((int) ($c->cantidad ?? 1) * (float) $c->precio_unitario, 2),
                ])->all(),
                'total' => ['Subtotal (sin IVA)', round($r->consumos->sum(fn ($c) => (int) ($c->cantidad ?? 1) * (float) $c->precio_unitario), 2)],
            ]],
            'Personal y Roles' => [[
                'titulo' => null,
                'encabezados' => ['Cédula', 'Nombre', 'Rol', 'Estado'],
                'filas' => $r->usuarios->map(fn ($u) => [
                    $u->numero_cedula,
                    $u->nombre,
                    $u->rol?->nombre ?? 'N/A',
                    $u->estado ? 'Activo' : 'Inactivo',
                ])->all(),
                'total' => ['Total usuarios', $r->usuarios->count()],
            ]],
            default => [],
        };
    }

    private function seccionExistencias(?string $titulo, Collection $productos): array
    {
        $existencias = self::existencias($productos);

        return [
            'titulo' => $titulo,
            'encabezados' => ['Nombre del Producto', 'Modelo', 'Categoría', 'Stock en Bodega'],
            'filas' => $existencias->map(fn ($item) => [
                $item['nombre'],
                $item['modelo'] ?? '—',
                $item['categoria'] ?? '—',
                $item['stock'],
            ])->all(),
            'total' => ['Total en stock', $existencias->sum('stock')],
        ];
    }
}
