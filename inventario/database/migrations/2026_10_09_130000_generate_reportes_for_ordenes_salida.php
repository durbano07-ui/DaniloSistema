<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Genera el reporte de Salidas y Despachos de cada orden de salida que aún no lo tiene.
     */
    public function up(): void
    {
        $consumoType = 'App\\Models\\Consumo';

        $ordenes = DB::table('ordenes_salida')->orderBy('id_orden')->get();

        foreach ($ordenes as $orden) {
            $consumoIds = DB::table('consumos')
                ->where('id_orden', $orden->id_orden)
                ->pluck('id_consumo');

            if ($consumoIds->isEmpty()) {
                continue;
            }

            $yaReportada = DB::table('reportables')
                ->where('reportable_type', $consumoType)
                ->whereIn('reportable_id', $consumoIds->map(fn ($id) => (string) $id))
                ->exists();

            if ($yaReportada) {
                continue;
            }

            $idReporte = DB::table('reportes')->insertGetId([
                'tipo' => 'Salidas y Despachos',
                'fecha' => $orden->fecha,
                'numero_cedula' => $orden->numero_cedula,
                'created_at' => $orden->created_at,
                'updated_at' => $orden->updated_at,
            ]);

            DB::table('reportables')->insert($consumoIds->map(fn ($id) => [
                'id_reporte' => $idReporte,
                'reportable_type' => $consumoType,
                'reportable_id' => (string) $id,
                'created_at' => $orden->created_at,
                'updated_at' => $orden->updated_at,
            ])->all());
        }
    }

    /**
     * Los reportes generados se conservan al revertir.
     */
    public function down(): void
    {
        //
    }
};
