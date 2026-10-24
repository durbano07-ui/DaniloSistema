<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordenes_salida', function (Blueprint $table) {
            $table->string('tipo', 20)->default('normal')->after('id_orden');
            $table->string('factura', 50)->nullable()->after('motivo');
            $table->text('nota')->nullable()->after('factura');
            $table->decimal('iva', 5, 4)->nullable()->after('nota');
            // Copia de los datos del formato vigentes al emitir el acta (entrega, recibe, destino...)
            $table->json('datos_acta')->nullable()->after('iva');
        });

        Schema::table('consumos', function (Blueprint $table) {
            $table->decimal('precio_unitario', 10, 2)->nullable()->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumos', function (Blueprint $table) {
            $table->dropColumn('precio_unitario');
        });

        Schema::table('ordenes_salida', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'factura', 'nota', 'iva', 'datos_acta']);
        });
    }
};
