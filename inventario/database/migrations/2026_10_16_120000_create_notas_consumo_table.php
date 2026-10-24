<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notas de seguimiento sobre cada equipo despachado.
     */
    public function up(): void
    {
        Schema::create('notas_consumo', function (Blueprint $table) {
            $table->id('id_nota');
            $table->unsignedBigInteger('id_consumo');
            $table->foreign('id_consumo')->references('id_consumo')->on('consumos')->cascadeOnDelete();
            $table->string('numero_cedula', 10);
            $table->foreign('numero_cedula')->references('numero_cedula')->on('usuarios');
            $table->text('texto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_consumo');
    }
};
