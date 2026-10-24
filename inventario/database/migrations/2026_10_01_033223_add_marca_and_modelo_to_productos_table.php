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
            $table->foreignId('id_marca')->nullable()->after('id_cat')->constrained('marcas', 'id_marca')->nullOnDelete();
            $table->foreignId('id_modelo')->nullable()->after('id_marca')->constrained('modelos', 'id_modelo')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['id_modelo']);
            $table->dropColumn('id_modelo');
            $table->dropForeign(['id_marca']);
            $table->dropColumn('id_marca');
        });
    }
};
