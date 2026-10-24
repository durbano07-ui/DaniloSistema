<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $almacenista = Usuario::where('numero_cedula', '0500000002')->first();
        $proveedor = Proveedor::first();
        $almacen = Almacen::first();
        $catLaptops = Categoria::where('nombre', 'Router')->first();
$catRouters = Categoria::where('nombre', 'Onu')->first();

        Producto::create([
            'numero_serie' => 'LAP-0001',
            'nombre' => 'Laptop Dell Inspiron',
            'precio' => 650.00,
            'estado' => 'disponible',
            'numero_cedula' => $almacenista->numero_cedula,
            'id_proveedor' => $proveedor->id_prov,
            'id_cat' => $catLaptops->id_cat,
            'id_almacen' => $almacen->id_almacen,
        ]);

        Producto::create([
            'numero_serie' => 'RTR-0001',
            'nombre' => 'Router TP-Link Archer',
            'precio' => 45.00,
            'estado' => 'disponible',
            'numero_cedula' => $almacenista->numero_cedula,
            'id_proveedor' => $proveedor->id_prov,
            'id_cat' => $catRouters->id_cat,
            'id_almacen' => $almacen->id_almacen,
        ]);
    }
}
