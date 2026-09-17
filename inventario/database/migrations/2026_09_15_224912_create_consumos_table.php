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
        Schema::create('consumos', function (Blueprint $table) {
            $table->id('id_consumo');
            $table->date('fecha');
            $table->string('motivo');
            $table->string('numero_serie', 50);
            $table->foreign('numero_serie')->references('numero_serie')->on('productos');
            $table->string('numero_cedula', 10);
            $table->foreign('numero_cedula')->references('numero_cedula')->on('usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumos');
    }
};
