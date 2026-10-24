<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;

class MarcaModeloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Router
        $catRouter = Categoria::where('nombre', 'Router')->first();
        if ($catRouter) {
            $m1 = Marca::firstOrCreate(
                ['id_cat' => $catRouter->id_cat, 'nombre' => 'TP-Link']
            );
            Modelo::firstOrCreate(['id_marca' => $m1->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'Archer C6']);
            Modelo::firstOrCreate(['id_marca' => $m1->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'TL-WR840N']);
            Modelo::firstOrCreate(['id_marca' => $m1->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'Archer AX10']);

            $m2 = Marca::firstOrCreate(
                ['id_cat' => $catRouter->id_cat, 'nombre' => 'MikroTik']
            );
            Modelo::firstOrCreate(['id_marca' => $m2->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'hEX RB750Gr3']);
            Modelo::firstOrCreate(['id_marca' => $m2->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'hAP ac2']);

            $m3 = Marca::firstOrCreate(
                ['id_cat' => $catRouter->id_cat, 'nombre' => 'Huawei']
            );
            Modelo::firstOrCreate(['id_marca' => $m3->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'WS5200']);
            Modelo::firstOrCreate(['id_marca' => $m3->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'WiFi AX3']);

            $m4 = Marca::firstOrCreate(
                ['id_cat' => $catRouter->id_cat, 'nombre' => 'Tenda']
            );
            Modelo::firstOrCreate(['id_marca' => $m4->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'AC10']);
            Modelo::firstOrCreate(['id_marca' => $m4->id_marca, 'id_cat' => $catRouter->id_cat, 'nombre' => 'TX3']);
        }

        // Onu
        $catOnu = Categoria::where('nombre', 'Onu')->first();
        if ($catOnu) {
            $m1 = Marca::firstOrCreate(
                ['id_cat' => $catOnu->id_cat, 'nombre' => 'Huawei']
            );
            Modelo::firstOrCreate(['id_marca' => $m1->id_marca, 'id_cat' => $catOnu->id_cat, 'nombre' => 'EchoLife HG8310M']);
            Modelo::firstOrCreate(['id_marca' => $m1->id_marca, 'id_cat' => $catOnu->id_cat, 'nombre' => 'OptiXstar EG8145V5']);

            $m2 = Marca::firstOrCreate(
                ['id_cat' => $catOnu->id_cat, 'nombre' => 'ZTE']
            );
            Modelo::firstOrCreate(['id_marca' => $m2->id_marca, 'id_cat' => $catOnu->id_cat, 'nombre' => 'ZXHN F601']);
            Modelo::firstOrCreate(['id_marca' => $m2->id_marca, 'id_cat' => $catOnu->id_cat, 'nombre' => 'ZXHN F670L']);

            $m3 = Marca::firstOrCreate(
                ['id_cat' => $catOnu->id_cat, 'nombre' => 'FiberHome']
            );
            Modelo::firstOrCreate(['id_marca' => $m3->id_marca, 'id_cat' => $catOnu->id_cat, 'nombre' => 'AN5506-01-A']);
            Modelo::firstOrCreate(['id_marca' => $m3->id_marca, 'id_cat' => $catOnu->id_cat, 'nombre' => 'AN5506-04-F']);

            $m4 = Marca::firstOrCreate(
                ['id_cat' => $catOnu->id_cat, 'nombre' => 'VSOL']
            );
            Modelo::firstOrCreate(['id_marca' => $m4->id_marca, 'id_cat' => $catOnu->id_cat, 'nombre' => 'V2801SG']);
        }

        // Spliter
        $catSpliter = Categoria::where('nombre', 'Spliter')->first();
        if ($catSpliter) {
            $m1 = Marca::firstOrCreate(
                ['id_cat' => $catSpliter->id_cat, 'nombre' => 'Optictimes']
            );
            Modelo::firstOrCreate(['id_marca' => $m1->id_marca, 'id_cat' => $catSpliter->id_cat, 'nombre' => 'PLC 1x8 SC/APC']);
            Modelo::firstOrCreate(['id_marca' => $m1->id_marca, 'id_cat' => $catSpliter->id_cat, 'nombre' => 'PLC 1x16 SC/APC']);

            $m2 = Marca::firstOrCreate(
                ['id_cat' => $catSpliter->id_cat, 'nombre' => 'Genérico']
            );
            Modelo::firstOrCreate(['id_marca' => $m2->id_marca, 'id_cat' => $catSpliter->id_cat, 'nombre' => 'Balanceado 1x2']);
            Modelo::firstOrCreate(['id_marca' => $m2->id_marca, 'id_cat' => $catSpliter->id_cat, 'nombre' => 'Desbalanceado 1x2 (80/20)']);
        }
    }
}
