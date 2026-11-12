<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seriales individuales generados para cada unidad de un lote del inventario.
     */
    public function up(): void
    {
        Schema::create('seriales_unidad', function (Blueprint $table) {
            $table->id('id_serial');
            $table->string('numero_serie', 50);
            $table->foreign('numero_serie')->references('numero_serie')->on('productos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('serial', 60)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seriales_unidad');
    }
};
