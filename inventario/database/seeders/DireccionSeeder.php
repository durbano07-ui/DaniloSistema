<?php

namespace Database\Seeders;

use App\Models\Direccion;
use App\Models\Ciudad;
use Illuminate\Database\Seeder;

class DireccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guaranda = Ciudad::where("nombre", "Guaranda")->first();
        
        Direccion::create([
            "calle" => "Via a Las Cochas",
            "id_ciudad" => $guaranda->id_ciudad,
        ]);
    }
}
