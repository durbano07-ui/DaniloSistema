<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Rol::where("nombre", "Administrador")->first();
        $almacenista = Rol::where("nombre", "Almacenista")->first();
        $tecnico = Rol::where("nombre", "Tecnico")->first();

        
        Usuario::create([
             'numero_cedula' => '0500000001',
            'nombre' => 'Ana Administradora',
            'id_rol' => $admin->id_rol,
            'password' => 'password123',
            'url_firma' => null,      
        ]);
        Usuario::create([
            'numero_cedula' => '0500000002',
            'nombre' => 'Beto Almacenista',
            'id_rol' => $almacenista->id_rol,
            'password' => 'password123',
            'url_firma' => null,
        ]);
        Usuario::create([
            'numero_cedula' => '0500000003',
            'nombre' => 'Carlos Tecnico',
            'id_rol' => $tecnico->id_rol,
            'password' => 'password123',
            'url_firma' => null,
        ]);
    }
}
