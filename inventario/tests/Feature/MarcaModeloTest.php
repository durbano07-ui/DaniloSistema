<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Almacen;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MarcaModeloTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (!Usuario::where('numero_cedula', '0500000002')->exists()) {
            $this->seed();
        }
    }

    public function test_almacenista_can_view_categorias_marcas_and_modelos(): void
    {
        $almacenista = Usuario::whereHas('rol', fn($q) => $q->where('nombre', 'Almacenista'))->first();

        $response = $this->actingAs($almacenista)->get(route('categorias.index'));

        $response->assertStatus(200);
        $response->assertSee('Categorías de Inventario');
    }

    public function test_almacenista_can_create_marca_and_modelo(): void
    {
        $almacenista = Usuario::whereHas('rol', fn($q) => $q->where('nombre', 'Almacenista'))->first();
        $categoria = Categoria::first();

        // 1. Crear Marca
        $resMarca = $this->actingAs($almacenista)->post(route('marcas.store'), [
            'id_cat' => $categoria->id_cat,
            'nombre' => 'NuevaMarcaTest',
        ]);
        $resMarca->assertRedirect();
        $this->assertDatabaseHas('marcas', [
            'id_cat' => $categoria->id_cat,
            'nombre' => 'NuevaMarcaTest',
        ]);

        $marca = Marca::where('nombre', 'NuevaMarcaTest')->first();

        // 2. Crear Modelo
        $resModelo = $this->actingAs($almacenista)->post(route('modelos.store'), [
            'id_cat' => $categoria->id_cat,
            'id_marca' => $marca->id_marca,
            'nombre' => 'ModeloTest-100',
        ]);
        $resModelo->assertRedirect();
        $this->assertDatabaseHas('modelos', [
            'id_marca' => $marca->id_marca,
            'nombre' => 'ModeloTest-100',
        ]);
    }

    public function test_can_create_product_with_marca_and_modelo(): void
    {
        $almacenista = Usuario::whereHas('rol', fn($q) => $q->where('nombre', 'Almacenista'))->first();
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        $marca = Marca::create([
            'id_cat' => $categoria->id_cat,
            'nombre' => 'MarcaProdTest',
        ]);

        $modelo = Modelo::create([
            'id_cat' => $categoria->id_cat,
            'id_marca' => $marca->id_marca,
            'nombre' => 'ModProdTest',
        ]);

        $res = $this->actingAs($almacenista)->post(route('productos.store'), [
            'numero_serie' => 'TEST-SN-MARCA-01',
            'nombre' => 'Equipo de Prueba Marca y Modelo',
            'id_cat' => $categoria->id_cat,
            'id_marca' => $marca->id_marca,
            'id_modelo' => $modelo->id_modelo,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'tipo' => 'unitario',
        ]);

        $res->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'numero_serie' => 'TEST-SN-MARCA-01',
            'id_marca' => $marca->id_marca,
            'id_modelo' => $modelo->id_modelo,
        ]);

        $prod = Producto::where('numero_serie', 'TEST-SN-MARCA-01')->first();
        $this->assertEquals('MarcaProdTest', $prod->marca->nombre);
        $this->assertEquals('ModProdTest', $prod->modelo->nombre);

        // Verificar visualización en la tabla de inventario
        $resIndex = $this->actingAs($almacenista)->get(route('productos.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('MarcaProdTest');
        $resIndex->assertSee('ModProdTest');
        $resIndex->assertSee('Bodega Central');
    }
}

