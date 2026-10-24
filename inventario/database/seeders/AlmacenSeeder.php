<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Usuario;
use App\Models\Direccion;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $almacenista = Usuario::where('numero_cedula', '0500000002')->first();
        $direccion = Direccion::first();

        Almacen::create([
            'nombre' => 'Bodega Central',
            'id_direccion' => $direccion->id_direccion,
            'numero_cedula' => $almacenista->numero_cedula,
        ]);
    }
}
