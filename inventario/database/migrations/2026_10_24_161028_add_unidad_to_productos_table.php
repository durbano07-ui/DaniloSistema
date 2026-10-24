<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unidad de medida del lote: 'unidades' (piezas) o 'metros' (cable).
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('unidad', 10)->default('unidades')->after('cantidad_disponible');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('unidad');
        });
    }
};
