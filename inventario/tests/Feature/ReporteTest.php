<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Almacen;
use App\Models\Proveedor;
use App\Models\Consumo;
use App\Models\Reporte;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReporteTest extends TestCase
{
    use DatabaseTransactions;

    protected ?Usuario $admin = null;
    protected ?Usuario $tecnico = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Usuario::where('numero_cedula', '0500000001')->exists()) {
            $this->seed();
        }

        $this->admin = Usuario::where('numero_cedula', '0500000001')->first();
        $this->tecnico = Usuario::where('numero_cedula', '0500000003')->first();
    }

    public function test_admin_puede_generar_reporte_de_salidas_y_despachos(): void
    {
        // 1. Crear consumo previo
        $categoria = Categoria::first();
        $almacen = Almacen::first();
        $proveedor = Proveedor::first();

        $producto = Producto::create([
            'numero_serie' => 'REP-SN-DESPACHO-01',
            'nombre' => 'Equipo para Reporte',
            'precio' => 199.99,
            'estado' => 'disponible',
            'id_cat' => $categoria->id_cat,
            'id_almacen' => $almacen->id_almacen,
            'id_proveedor' => $proveedor->id_prov,
            'numero_cedula' => $this->admin->numero_cedula,
        ]);

        $consumo = Consumo::create([
            'fecha' => '2026-09-30',
            'motivo' => 'Instalación corporativa nodo principal',
            'numero_serie' => $producto->numero_serie,
            'numero_cedula' => $this->tecnico->numero_cedula,
        ]);
        $producto->update(['estado' => 'consumido']);

        // 2. Generar reporte tipo Salidas y Despachos
        $response = $this->actingAs($this->admin)->post(route('reportes.store'), [
            'tipo' => 'Salidas y Despachos',
            'fecha' => '2026-09-30',
            'fecha_desde' => '2026-09-01',
            'fecha_hasta' => '2026-09-30',
            'numero_cedula_tecnico' => $this->tecnico->numero_cedula,
        ]);

        $reporte = Reporte::where('tipo', 'Salidas y Despachos')->latest('id_reporte')->firstOrFail();
        $response->assertRedirect(route('reportes.show', $reporte));

        // 3. Verificar que el consumo está vinculado al reporte
        $this->assertTrue($reporte->consumos->contains('id_consumo', $consumo->id_consumo));

        // 4. Verificar que se visualiza correctamente en la vista show
        $viewResponse = $this->actingAs($this->admin)->get(route('reportes.show', $reporte));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Salidas y Despachos');
        $viewResponse->assertSee('REP-SN-DESPACHO-01');
        $viewResponse->assertSee($this->tecnico->nombre);
        $viewResponse->assertSee('Total Equipos Retirados');
    }
}
