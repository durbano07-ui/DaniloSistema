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
        Schema::create('telf', function (Blueprint $table) {
            $table->id('id_telf');
            $table->string('numero');
            $table->string('telefonable_type');
            $table->string('telefonable_id');
            $table->index(['telefonable_type', 'telefonable_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telf');
    }
};
