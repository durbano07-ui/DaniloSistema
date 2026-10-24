<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categoria::create(["nombre" => "Router", "stock" => 0]);
        Categoria::create(["nombre" => "Onu", "stock" => 0]);
        Categoria::create(["nombre" => "Spliter", "stock" => 0]);
    }
}
