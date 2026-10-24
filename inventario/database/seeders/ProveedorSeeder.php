<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Usuario::where('numero_cedula', '0500000001')->first();

        Proveedor::create([
            'nombre' => 'TecnoImport S.A.',
            'numero_cedula' => $admin->numero_cedula,
        ]);
    }
}
