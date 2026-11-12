<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\SerialUnidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SerialesLoteTest extends TestCase
{
    use DatabaseTransactions;

    protected ?Usuario $tecnico = null;

    protected ?Usuario $almacenista = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Usuario::where('numero_cedula', '0500000003')->exists()) {
            $this->seed();
        }

        $this->tecnico = Usuario::where('numero_cedula', '0500000003')->first();
        $this->almacenista = Usuario::where('numero_cedula', '0500000002')->first();
    }

    private function crearLote(string $serie, int $cantidad, string $unidad = 'unidades'): Producto
    {
        return Producto::create([
            'numero_serie' => $serie,
            'nombre' => 'Conectores SC/APC',
            'precio' => 0.5,
            'estado' => 'disponible',
            'tipo' => 'lote',
            'unidad' => $unidad,
            'cantidad' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'id_cat' => Categoria::first()->id_cat,
            'id_almacen' => Almacen::first()->id_almacen,
            'id_proveedor' => Proveedor::first()->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);
    }

    public function test_la_etiqueta_del_inventario_ofrece_codigos_por_unidad(): void
    {
        $this->actingAs($this->almacenista)->get(route('productos.index'))
            ->assertOk()
            ->assertSee('¿Desea ver un código para cada equipo?');
    }

    public function test_generar_codigos_crea_uno_por_unidad_y_no_duplica(): void
    {
        $lote = $this->crearLote('LOTE-SC-12', 12);

        $this->actingAs($this->almacenista)->postJson(route('productos.seriales.store', $lote))
            ->assertOk()
            ->assertJsonCount(12, 'seriales')
            ->assertJsonPath('seriales.0', 'LOTE-SC-12-01')
            ->assertJsonPath('seriales.11', 'LOTE-SC-12-12');

        // Repetir la petición no duplica los códigos
        $this->actingAs($this->almacenista)->postJson(route('productos.seriales.store', $lote))->assertOk();
        $this->assertSame(12, $lote->seriales()->count());

        // Se vuelven a consultar desde la misma etiqueta
        $this->actingAs($this->almacenista)->getJson(route('productos.seriales.index', $lote))
            ->assertOk()
            ->assertJsonCount(12, 'seriales');
    }

    public function test_si_el_lote_crece_solo_se_generan_los_faltantes(): void
    {
        $lote = $this->crearLote('LOTE-SC-5', 5);
        $this->actingAs($this->almacenista)->postJson(route('productos.seriales.store', $lote));

        $lote->update(['cantidad' => 7, 'cantidad_disponible' => 7]);

        $this->actingAs($this->almacenista)->postJson(route('productos.seriales.store', $lote))
            ->assertOk()
            ->assertJsonCount(7, 'seriales')
            ->assertJsonPath('seriales.6', 'LOTE-SC-5-07');
    }

    public function test_lote_en_metros_no_genera_codigos_por_unidad(): void
    {
        $lote = $this->crearLote('BOB-FIBRA-1000', 1000, 'metros');

        $this->actingAs($this->almacenista)->postJson(route('productos.seriales.store', $lote))->assertUnprocessable();
        $this->assertSame(0, SerialUnidad::where('numero_serie', 'BOB-FIBRA-1000')->count());
    }

    public function test_escanear_codigo_de_unidad_en_salida_devuelve_su_lote(): void
    {
        $lote = $this->crearLote('LOTE-SC-12', 12);
        $this->actingAs($this->almacenista)->postJson(route('productos.seriales.store', $lote));

        $this->actingAs($this->almacenista)->getJson(route('productos.scan', 'LOTE-SC-12-03'))
            ->assertOk()
            ->assertJsonPath('encontrado', true)
            ->assertJsonPath('producto.numero_serie', 'LOTE-SC-12')
            ->assertJsonPath('serial_unidad', 'LOTE-SC-12-03');
    }

    public function test_no_se_puede_ingresar_un_equipo_con_el_codigo_de_una_unidad(): void
    {
        $lote = $this->crearLote('LOTE-SC-12', 12);
        $this->actingAs($this->almacenista)->postJson(route('productos.seriales.store', $lote));

        $this->actingAs($this->almacenista)->post(route('productos.store'), [
            'numero_serie' => 'LOTE-SC-12-01',
            'nombre' => 'Equipo duplicado',
            'condicion' => 'nuevo',
            'tipo' => 'unitario',
            'id_cat' => Categoria::first()->id_cat,
            'id_almacen' => Almacen::first()->id_almacen,
            'id_proveedor' => Proveedor::first()->id_prov,
        ])->assertSessionHasErrors('numero_serie');
    }

    public function test_tecnico_no_puede_generar_codigos(): void
    {
        $lote = $this->crearLote('LOTE-SC-12', 12);

        $this->actingAs($this->tecnico)->postJson(route('productos.seriales.store', $lote))->assertForbidden();
    }
}
