<?php

namespace App\Support;

use App\Models\Consumo;
use App\Models\OrdenSalida;
use App\Models\Producto;
use App\Models\Reporte;
use Illuminate\Support\Facades\DB;

/**
 * Registra una orden de salida: descuenta el stock de cada equipo y genera su reporte.
 */
class DespachoService
{
    /**
     * @param  array  $datosOrden  Atributos de la orden (fecha, motivo, tipo, factura...)
     * @param  array  $items  Lista de ['numero_serie', 'cantidad', 'precio_unitario'?]
     * @param  string  $cedulaResponsable  Usuario al que se asigna la salida
     * @param  string  $cedulaEmisor  Usuario que registra la salida (firma el reporte)
     * @return array{0: OrdenSalida, 1: array<int, Consumo>}
     */
    public static function registrar(array $datosOrden, array $items, string $cedulaResponsable, string $cedulaEmisor): array
    {
        return DB::transaction(function () use ($datosOrden, $items, $cedulaResponsable, $cedulaEmisor) {
            $orden = OrdenSalida::create($datosOrden + ['numero_cedula' => $cedulaResponsable]);
            $creados = [];

            foreach ($items as $item) {
                $prod = Producto::where('numero_serie', $item['numero_serie'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($prod->estado !== 'disponible') {
                    abort(422, "El equipo {$prod->nombre} ({$prod->numero_serie}) ya no se encuentra disponible.");
                }

                $cantidadRetiro = max(1, (int) ($item['cantidad'] ?? 1));

                if ($prod->tipo === 'lote') {
                    if ($cantidadRetiro > $prod->cantidad_disponible) {
                        abort(422, "Solo quedan {$prod->cantidad_disponible} unidades disponibles del lote {$prod->nombre}.");
                    }
                } else {
                    $cantidadRetiro = 1;
                }

                // 1. Crear el registro de consumo / salida
                $creados[] = Consumo::create([
                    'id_orden' => $orden->id_orden,
                    'fecha' => $orden->fecha,
                    'motivo' => $orden->motivo,
                    'numero_serie' => $prod->numero_serie,
                    'numero_cedula' => $cedulaResponsable,
                    'cantidad' => $cantidadRetiro,
                    'precio_unitario' => $item['precio_unitario'] ?? null,
                ]);

                // 2. Actualizar el estado o cantidades del producto
                if ($prod->tipo === 'lote') {
                    $nuevaDisp = $prod->cantidad_disponible - $cantidadRetiro;
                    $prod->update([
                        'cantidad_disponible' => max(0, $nuevaDisp),
                        'estado' => ($nuevaDisp <= 0) ? 'consumido' : 'disponible',
                    ]);
                } else {
                    $prod->update(['estado' => 'consumido']);
                }
            }

            // Cada orden de salida genera automáticamente su reporte (con factura o normal)
            $reporte = Reporte::create([
                'tipo' => $orden->tipo === 'factura' ? 'Salidas con Factura' : 'Salidas y Despachos',
                'fecha' => $orden->fecha,
                'numero_cedula' => $cedulaEmisor,
            ]);
            $reporte->consumos()->attach(collect($creados)->pluck('id_consumo')->all());

            return [$orden, $creados];
        });
    }
}
