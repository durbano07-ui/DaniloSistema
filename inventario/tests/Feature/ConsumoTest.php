<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Consumo;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConsumoTest extends TestCase
{
    use DatabaseTransactions;

    protected ?Usuario $tecnico = null;

    protected ?Usuario $admin = null;

    protected ?Usuario $almacenista = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Usuario::where('numero_cedula', '0500000003')->exists()) {
            $this->seed();
        }

        $this->tecnico = Usuario::where('numero_cedula', '0500000003')->first();
        $this->admin = Usuario::where('numero_cedula', '0500000001')->first();
        $this->almacenista = Usuario::where('numero_cedula', '0500000002')->first();
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $response = $this->get(route('consumos.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_tecnico_puede_acceder_al_modulo_de_consumos(): void
    {
        $response = $this->actingAs($this->tecnico)->get(route('consumos.index'));
        $response->assertStatus(200);
        $response->assertSee('Registro de Consumo Técnico (CU-05)');
    }

    public function test_tecnico_puede_ver_formulario_nuevo_retiro(): void
    {
        $response = $this->actingAs($this->tecnico)->get(route('consumos.create'));
        $response->assertStatus(200);
        $response->assertSee('Terminal de Salida por Código de Barras');
    }

    public function test_registro_de_consumo_descuenta_stock_y_cambia_estado_a_consumido(): void
    {
        // Producto de prueba disponible
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        $producto = Producto::create([
            'numero_serie' => 'TEST-SN-9999',
            'nombre' => 'Equipo de Prueba Unitario',
            'precio' => 120.50,
            'estado' => 'disponible',
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        $categoria->refresh();
        $stockAntes = $categoria->stock;

        $response = $this->actingAs($this->tecnico)->post(route('consumos.store'), [
            'numero_serie' => $producto->numero_serie,
            'fecha' => '2026-09-30',
            'motivo' => 'Instalación cliente residencial fibra óptica #101',
        ]);

        $consumo = Consumo::where('numero_serie', 'TEST-SN-9999')->firstOrFail();
        $response->assertRedirect(route('consumos.show', $consumo));
        $response->assertSessionHas('status');

        // Verificar que el producto cambió a consumido
        $producto->refresh();
        $this->assertEquals('consumido', $producto->estado);

        // Verificar que el stock de la categoría disminuyó en 1
        $categoria->refresh();
        $this->assertEquals($stockAntes - 1, $categoria->stock);

        // Verificar registro de consumo
        $this->assertDatabaseHas('consumos', [
            'numero_serie' => 'TEST-SN-9999',
            'numero_cedula' => $this->tecnico->numero_cedula,
            'motivo' => 'Instalación cliente residencial fibra óptica #101',
        ]);
    }

    public function test_no_se_puede_consumir_producto_que_no_esta_disponible(): void
    {
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        $producto = Producto::create([
            'numero_serie' => 'TEST-SN-AVERIADO',
            'nombre' => 'Equipo Dañado',
            'precio' => 80.00,
            'estado' => 'averiado',
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        $response = $this->actingAs($this->tecnico)->post(route('consumos.store'), [
            'numero_serie' => $producto->numero_serie,
            'fecha' => '2026-09-30',
            'motivo' => 'Intento de retiro inválido',
        ]);

        $response->assertSessionHasErrors('numero_serie');
    }

    public function test_anulacion_de_consumo_restaura_stock_y_regresa_producto_a_disponible(): void
    {
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        $producto = Producto::create([
            'numero_serie' => 'TEST-SN-REVERT',
            'nombre' => 'Switch Gigabit 8 Puertos',
            'precio' => 45.00,
            'estado' => 'disponible',
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        // Registrar consumo
        $this->actingAs($this->tecnico)->post(route('consumos.store'), [
            'numero_serie' => $producto->numero_serie,
            'fecha' => '2026-09-30',
            'motivo' => 'Entrega temporal',
        ]);

        $consumo = Consumo::where('numero_serie', 'TEST-SN-REVERT')->firstOrFail();
        $categoria->refresh();
        $stockTrasConsumo = $categoria->stock;

        // Anular consumo
        $response = $this->actingAs($this->tecnico)->delete(route('consumos.destroy', $consumo));
        $response->assertRedirect(route('consumos.index'));
        $response->assertSessionHas('status');

        // Producto regresa a disponible
        $producto->refresh();
        $this->assertEquals('disponible', $producto->estado);

        // Stock aumenta en 1
        $categoria->refresh();
        $this->assertEquals($stockTrasConsumo + 1, $categoria->stock);

        // Consumo eliminado
        $this->assertDatabaseMissing('consumos', [
            'id_consumo' => $consumo->id_consumo,
        ]);
    }

    public function test_tecnico_puede_ver_comprobante_de_retiro(): void
    {
        $consumo = Consumo::with('producto')->first();
        if (! $consumo) {
            $prod = Producto::where('estado', 'disponible')->first();
            $consumo = Consumo::create([
                'fecha' => '2026-09-30',
                'motivo' => 'Verificación de vale',
                'numero_serie' => $prod->numero_serie,
                'numero_cedula' => $this->tecnico->numero_cedula,
            ]);
            $prod->update(['estado' => 'consumido']);
        }

        $response = $this->actingAs($this->tecnico)->get(route('consumos.show', $consumo));
        $response->assertStatus(200);
        $response->assertSee('VALE #CNS-');
        $response->assertSee($consumo->numero_serie);
        $response->assertSee('Técnico Receptor');
    }

    public function test_almacenista_puede_acceder_a_registro_de_salidas(): void
    {
        $response = $this->actingAs($this->almacenista)->get(route('consumos.index'));
        $response->assertStatus(200);
    }

    public function test_escanear_codigo_de_barras_retorna_datos_del_equipo(): void
    {
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        $producto = Producto::create([
            'numero_serie' => 'SCAN-TEST-001',
            'nombre' => 'Módem Óptico GPON',
            'precio' => 55.00,
            'estado' => 'disponible',
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        $response = $this->actingAs($this->tecnico)->get(route('productos.scan', 'SCAN-TEST-001'));
        $response->assertStatus(200);
        $response->assertJson([
            'encontrado' => true,
            'es_disponible' => true,
            'producto' => [
                'numero_serie' => 'SCAN-TEST-001',
                'nombre' => 'Módem Óptico GPON',
            ],
        ]);
    }

    public function test_registro_de_lote_y_retiro_parcial_actualiza_cantidades_y_stock(): void
    {
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        $categoria->refresh();
        $stockOriginal = $categoria->stock;

        // 1. Crear Lote de 100 herrajes
        $producto = Producto::create([
            'numero_serie' => 'LOTE-HERR-100',
            'nombre' => 'Herrajes de Suspensión para Fibra Óptica',
            'precio' => 0.85,
            'estado' => 'disponible',
            'tipo' => 'lote',
            'cantidad' => 100,
            'cantidad_disponible' => 100,
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        // Verificar que el observador sumó 100 al stock de la categoría
        $categoria->refresh();
        $this->assertEquals($stockOriginal + 100, $categoria->stock);

        // 2. Retiro parcial de 15 herrajes
        $response = $this->actingAs($this->tecnico)->post(route('consumos.store'), [
            'numero_serie' => 'LOTE-HERR-100',
            'fecha' => '2026-09-30',
            'motivo' => '15 herrajes para instalación en Poste #40',
            'cantidad' => 15,
        ]);

        $consumo = Consumo::where('numero_serie', 'LOTE-HERR-100')->firstOrFail();
        $response->assertRedirect(route('consumos.show', $consumo));
        $this->assertEquals(15, $consumo->cantidad);

        // El lote sigue disponible pero con 85 unidades
        $producto->refresh();
        $this->assertEquals('disponible', $producto->estado);
        $this->assertEquals(85, $producto->cantidad_disponible);

        // El stock de la categoría disminuyó en 15
        $categoria->refresh();
        $this->assertEquals($stockOriginal + 85, $categoria->stock);

        // 3. Verificar que aparece en la misma vista de inventario con 85 unids.
        $resIndex = $this->actingAs($this->almacenista)->get(route('productos.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('85 unids.');
        $resIndex->assertSee('Herrajes de Suspensión');

        // 4. Retiro del remanente (85 unidades)
        $this->actingAs($this->tecnico)->post(route('consumos.store'), [
            'numero_serie' => 'LOTE-HERR-100',
            'fecha' => '2026-09-30',
            'motivo' => 'Cierre de obra posteado final',
            'cantidad' => 85,
        ]);

        // Ahora el lote queda totalmente consumido (0 disponibles)
        $producto->refresh();
        $this->assertEquals('consumido', $producto->estado);
        $this->assertEquals(0, $producto->cantidad_disponible);

        $categoria->refresh();
        $this->assertEquals($stockOriginal, $categoria->stock);
    }

    public function test_lote_de_cable_en_metros_se_registra_y_retira_por_metros(): void
    {
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        // 1. Ingreso de una bobina de 500 m de cable
        $this->actingAs($this->almacenista)->post(route('productos.store'), [
            'numero_serie' => 'BOB-DROP-500',
            'nombre' => 'Cable Drop Fibra Óptica 1 Hilo',
            'condicion' => 'nuevo',
            'tipo' => 'lote',
            'unidad' => 'metros',
            'cantidad' => 500,
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
        ])->assertRedirect(route('productos.index'));

        $producto = Producto::findOrFail('BOB-DROP-500');
        $this->assertEquals('metros', $producto->unidad);
        $this->assertEquals(500, $producto->cantidad_disponible);

        // 2. Retiro de 120 m para una instalación
        $this->actingAs($this->tecnico)->post(route('consumos.store'), [
            'numero_serie' => 'BOB-DROP-500',
            'fecha' => '2026-10-24',
            'motivo' => 'Acometida cliente #202',
            'cantidad' => 120,
        ]);

        $producto->refresh();
        $this->assertEquals(380, $producto->cantidad_disponible);
        $this->assertEquals('disponible', $producto->estado);

        // 3. El inventario y el comprobante muestran metros, no unidades
        $this->actingAs($this->almacenista)->get(route('productos.index', ['buscar' => 'BOB-DROP-500']))
            ->assertSee('Lote: 380 m');

        $consumo = Consumo::where('numero_serie', 'BOB-DROP-500')->firstOrFail();
        $this->actingAs($this->tecnico)->get(route('consumos.show', $consumo))
            ->assertSee('120 m de Cable Drop');
    }

    public function test_equipo_individual_siempre_se_registra_en_unidades(): void
    {
        $this->actingAs($this->almacenista)->post(route('productos.store'), [
            'numero_serie' => 'ONT-UNIT-001',
            'nombre' => 'ONT Huawei',
            'condicion' => 'nuevo',
            'tipo' => 'unitario',
            'unidad' => 'metros',
            'id_cat' => Categoria::first()->id_cat,
            'id_almacen' => Almacen::first()->id_almacen,
            'id_proveedor' => Proveedor::first()->id_prov,
        ]);

        $this->assertEquals('unidades', Producto::findOrFail('ONT-UNIT-001')->unidad);
    }

    public function test_filtro_por_tipo_en_vista_de_inventario(): void
    {
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        // Crear producto unitario y producto lote
        Producto::create([
            'numero_serie' => 'FILTRO-UNI-1',
            'nombre' => 'Router Monitoreo Individual',
            'estado' => 'disponible',
            'tipo' => 'unitario',
            'cantidad' => 1,
            'cantidad_disponible' => 1,
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        Producto::create([
            'numero_serie' => 'FILTRO-LOTE-1',
            'nombre' => 'Conectores SC APC en Lote',
            'estado' => 'disponible',
            'tipo' => 'lote',
            'cantidad' => 50,
            'cantidad_disponible' => 50,
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        // Filtrar por lote
        $resLote = $this->actingAs($this->almacenista)->get(route('productos.index', ['tipo' => 'lote']));
        $resLote->assertStatus(200);
        $resLote->assertSee('Conectores SC APC en Lote');
        $resLote->assertDontSee('Router Monitoreo Individual');

        // Filtrar por unitario
        $resUni = $this->actingAs($this->almacenista)->get(route('productos.index', ['tipo' => 'unitario']));
        $resUni->assertStatus(200);
        $resUni->assertSee('Router Monitoreo Individual');
        $resUni->assertDontSee('Conectores SC APC en Lote');
    }

    public function test_retiro_acumulativo_de_multiples_productos_en_un_solo_pedido(): void
    {
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        // 1. Crear 2 productos unitarios y 1 lote
        $prod1 = Producto::create([
            'numero_serie' => 'MULTI-ORD-001',
            'nombre' => 'Módem Router WiFi 6',
            'precio' => 45.00,
            'estado' => 'disponible',
            'tipo' => 'unitario',
            'cantidad' => 1,
            'cantidad_disponible' => 1,
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        $prod2 = Producto::create([
            'numero_serie' => 'MULTI-ORD-002',
            'nombre' => 'Decodificador IPTV 4K',
            'precio' => 60.00,
            'estado' => 'disponible',
            'tipo' => 'unitario',
            'cantidad' => 1,
            'cantidad_disponible' => 1,
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        $prodLote = Producto::create([
            'numero_serie' => 'MULTI-LOTE-003',
            'nombre' => 'Patch Cords Fibra 2m',
            'precio' => 3.50,
            'estado' => 'disponible',
            'tipo' => 'lote',
            'cantidad' => 20,
            'cantidad_disponible' => 20,
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->almacenista->numero_cedula,
        ]);

        // 2. Enviar pedido con tabla acumulativa de 3 productos
        $response = $this->actingAs($this->tecnico)->post(route('consumos.store'), [
            'fecha' => '2026-09-30',
            'motivo' => 'Despacho masivo para cuadrilla de instalación #4',
            'items' => [
                ['numero_serie' => 'MULTI-ORD-001', 'cantidad' => 1],
                ['numero_serie' => 'MULTI-ORD-002', 'cantidad' => 1],
                ['numero_serie' => 'MULTI-LOTE-003', 'cantidad' => 5],
            ],
        ]);

        // Con múltiples ítems redirige al índice con mensaje de éxito
        $response->assertRedirect(route('consumos.index'));
        $response->assertSessionHas('status');

        // 3. Verificar estados de los productos unitarios
        $prod1->refresh();
        $this->assertEquals('consumido', $prod1->estado);

        $prod2->refresh();
        $this->assertEquals('consumido', $prod2->estado);

        // 4. Verificar lote actualizado
        $prodLote->refresh();
        $this->assertEquals('disponible', $prodLote->estado);
        $this->assertEquals(15, $prodLote->cantidad_disponible);

        // 5. Verificar registros de consumo creados en base de datos
        $this->assertDatabaseHas('consumos', [
            'numero_serie' => 'MULTI-ORD-001',
            'cantidad' => 1,
            'motivo' => 'Despacho masivo para cuadrilla de instalación #4',
        ]);
        $this->assertDatabaseHas('consumos', [
            'numero_serie' => 'MULTI-ORD-002',
            'cantidad' => 1,
            'motivo' => 'Despacho masivo para cuadrilla de instalación #4',
        ]);
        $this->assertDatabaseHas('consumos', [
            'numero_serie' => 'MULTI-LOTE-003',
            'cantidad' => 5,
            'motivo' => 'Despacho masivo para cuadrilla de instalación #4',
        ]);
    }
}
