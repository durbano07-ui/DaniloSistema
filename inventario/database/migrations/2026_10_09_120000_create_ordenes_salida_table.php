<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ordenes_salida', function (Blueprint $table) {
            $table->id('id_orden');
            $table->date('fecha');
            $table->string('motivo');
            $table->string('numero_cedula', 10);
            $table->foreign('numero_cedula')->references('numero_cedula')->on('usuarios');
            $table->timestamps();
        });

        Schema::table('consumos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_orden')->nullable()->after('id_consumo');
            $table->foreign('id_orden')->references('id_orden')->on('ordenes_salida')->cascadeOnDelete();
        });

        // Agrupa los consumos existentes en órdenes: mismo técnico, fecha, motivo y momento de registro
        $grupos = DB::table('consumos')
            ->orderBy('id_consumo')
            ->get()
            ->groupBy(fn ($c) => $c->numero_cedula . '|' . $c->fecha . '|' . $c->motivo . '|' . $c->created_at);

        foreach ($grupos as $consumos) {
            $primero = $consumos->first();
            $idOrden = DB::table('ordenes_salida')->insertGetId([
                'fecha' => $primero->fecha,
                'motivo' => $primero->motivo,
                'numero_cedula' => $primero->numero_cedula,
                'created_at' => $primero->created_at,
                'updated_at' => $primero->updated_at,
            ]);

            DB::table('consumos')
                ->whereIn('id_consumo', $consumos->pluck('id_consumo'))
                ->update(['id_orden' => $idOrden]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumos', function (Blueprint $table) {
            $table->dropForeign(['id_orden']);
            $table->dropColumn('id_orden');
        });

        Schema::dropIfExists('ordenes_salida');
    }
};
