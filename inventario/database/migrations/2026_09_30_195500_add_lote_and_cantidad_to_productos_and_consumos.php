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
        Schema::table('productos', function (Blueprint $table) {
            $table->string('tipo', 20)->default('unitario')->after('nombre');
            $table->unsignedInteger('cantidad')->default(1)->after('tipo');
            $table->unsignedInteger('cantidad_disponible')->default(1)->after('cantidad');
        });

        Schema::table('consumos', function (Blueprint $table) {
            $table->unsignedInteger('cantidad')->default(1)->after('numero_serie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'cantidad', 'cantidad_disponible']);
        });

        Schema::table('consumos', function (Blueprint $table) {
            $table->dropColumn(['cantidad']);
        });
    }
};
