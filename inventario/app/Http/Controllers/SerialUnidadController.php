<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\SerialUnidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Seriales por unidad de un lote (LOTE-01, LOTE-02, ...), consultados y generados desde la etiqueta del inventario.
 */
class SerialUnidadController extends Controller
{
    /**
     * Lista los seriales ya guardados de un lote.
     */
    public function index(Producto $producto): JsonResponse
    {
        return response()->json($this->respuesta($producto));
    }

    /**
     * Genera los seriales que falten hasta completar la cantidad total del lote.
     */
    public function store(Producto $producto): JsonResponse
    {
        if (! $this->admiteSeriales($producto)) {
            return response()->json([
                'mensaje' => 'Solo se generan códigos por unidad para lotes contados en unidades.',
            ], 422);
        }

        DB::transaction(function () use ($producto) {
            // Bloquea el lote para que dos peticiones simultáneas no generen el mismo serial
            Producto::whereKey($producto->numero_serie)->lockForUpdate()->first();

            $cantidad = (int) $producto->cantidad;
            $faltantes = $cantidad - $producto->seriales()->count();
            if ($faltantes <= 0) {
                return;
            }

            $ancho = max(2, strlen((string) $cantidad));
            $ocupados = SerialUnidad::where('serial', 'like', $producto->numero_serie.'-%')->pluck('serial')->flip();

            $n = 0;
            while ($faltantes > 0) {
                $serial = $producto->numero_serie.'-'.str_pad((string) ++$n, $ancho, '0', STR_PAD_LEFT);
                if ($ocupados->has($serial) || Producto::whereKey($serial)->exists()) {
                    continue;
                }
                $producto->seriales()->create(['serial' => $serial]);
                $faltantes--;
            }
        });

        return response()->json($this->respuesta($producto));
    }

    private function admiteSeriales(Producto $producto): bool
    {
        return $producto->tipo === 'lote' && ! $producto->esMetros();
    }

    /**
     * @return array{admite: bool, cantidad: int, seriales: array<int, string>}
     */
    private function respuesta(Producto $producto): array
    {
        return [
            'admite' => $this->admiteSeriales($producto),
            'cantidad' => (int) $producto->cantidad,
            'seriales' => $producto->seriales()->pluck('serial')->all(),
        ];
    }
}
