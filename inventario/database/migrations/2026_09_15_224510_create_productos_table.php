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
        Schema::create('productos', function (Blueprint $table) {
            $table->string('numero_serie', 50)->primary();
            $table->string('nombre');
            $table->decimal('precio', 10, 2);
            $table->string('url_img')->nullable();
            $table->string('estado')->default('disponible');

            $table->string('numero_cedula', 10);
            $table->foreign('numero_cedula')->references('numero_cedula')->on('usuarios');

            $table->foreignId('id_proveedor')->constrained('proveedores', 'id_prov');
            $table->foreignId('id_cat')->constrained('categorias', 'id_cat');
            $table->foreignId('id_almacen')->constrained('almacenes', 'id_almacen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
