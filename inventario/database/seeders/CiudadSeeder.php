<?php

namespace Database\Seeders;

use App\Models\Ciudad;
use Illuminate\Database\Seeder;

class CiudadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ciudad::create(["nombre" => "Guaranda","provincia" =>"Bolivar"]);
        Ciudad::create(["nombre" => "San Miguel","provincia" =>"Bolivar"]);
    }
}
