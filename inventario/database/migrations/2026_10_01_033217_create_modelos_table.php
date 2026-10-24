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
        Schema::create('modelos', function (Blueprint $table) {
            $table->id('id_modelo');
            $table->foreignId('id_marca')->constrained('marcas', 'id_marca')->cascadeOnDelete();
            $table->foreignId('id_cat')->constrained('categorias', 'id_cat')->cascadeOnDelete();
            $table->string('nombre');
            $table->timestamps();

            $table->unique(['id_marca', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modelos');
    }
};
