<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los reportes generados por órdenes con factura pasan al tipo "Salidas con Factura".
     */
    public function up(): void
    {
        $consumosFactura = DB::table('consumos')
            ->join('ordenes_salida', 'ordenes_salida.id_orden', '=', 'consumos.id_orden')
            ->where('ordenes_salida.tipo', 'factura')
            ->pluck('consumos.id_consumo')
            ->map(fn ($id) => (string) $id);

        $reportes = DB::table('reportables')
            ->where('reportable_type', 'App\\Models\\Consumo')
            ->whereIn('reportable_id', $consumosFactura)
            ->distinct()
            ->pluck('id_reporte');

        DB::table('reportes')
            ->whereIn('id_reporte', $reportes)
            ->where('tipo', 'Salidas y Despachos')
            ->update(['tipo' => 'Salidas con Factura']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('reportes')
            ->where('tipo', 'Salidas con Factura')
            ->update(['tipo' => 'Salidas y Despachos']);
    }
};
