<?php

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;

beforeEach(function () {
    if (! Usuario::where('numero_cedula', '0500000003')->exists()) {
        $this->seed();
    }

    $this->almacenista = Usuario::findOrFail('0500000002');
    $this->categoria = Categoria::firstOrFail();
});

function datosIngresoMultiple(array $cambios = []): array
{
    return array_merge([
        'nombre' => 'ONT Huawei HG8145',
        'condicion' => 'nuevo',
        'tipo' => 'unitario',
        'id_cat' => Categoria::firstOrFail()->id_cat,
        'id_almacen' => Almacen::firstOrFail()->id_almacen,
        'id_proveedor' => Proveedor::firstOrFail()->id_prov,
    ], $cambios);
}

test('un QR con varias series registra un equipo por cada serie con la misma clasificación', function () {
    $stockInicial = $this->categoria->stock;

    $this->actingAs($this->almacenista)
        ->post(route('productos.store'), datosIngresoMultiple([
            'numeros_serie' => ['QR-SN-001', ' QR-SN-002 ', 'QR-SN-003', ''],
        ]))
        ->assertRedirect(route('productos.index'))
        ->assertSessionHas('status', '3 equipos registrados exitosamente en el inventario.');

    $productos = Producto::whereIn('numero_serie', ['QR-SN-001', 'QR-SN-002', 'QR-SN-003'])->get();

    expect($productos)->toHaveCount(3)
        ->and($productos->pluck('nombre')->unique()->all())->toBe(['ONT Huawei HG8145'])
        ->and($productos->pluck('tipo')->unique()->all())->toBe(['unitario'])
        ->and($this->categoria->fresh()->stock)->toBe($stockInicial + 3);
});

test('si una serie de la lista ya existe no se registra ninguna', function () {
    $this->actingAs($this->almacenista)
        ->post(route('productos.store'), datosIngresoMultiple(['numeros_serie' => ['QR-DUP-001']]));

    $this->actingAs($this->almacenista)
        ->post(route('productos.store'), datosIngresoMultiple([
            'numeros_serie' => ['QR-DUP-002', 'QR-DUP-001'],
        ]))
        ->assertSessionHasErrors('numeros_serie.1');

    expect(Producto::where('numero_serie', 'QR-DUP-002')->exists())->toBeFalse();
});

test('las series repetidas dentro de la misma lista se rechazan', function () {
    $this->actingAs($this->almacenista)
        ->post(route('productos.store'), datosIngresoMultiple([
            'numeros_serie' => ['QR-REP-001', 'QR-REP-001'],
        ]))
        ->assertSessionHasErrors('numeros_serie.0');

    expect(Producto::where('numero_serie', 'QR-REP-001')->exists())->toBeFalse();
});

test('el formulario de ingreso conserva la lista de series tras un error de validación', function () {
    $this->actingAs($this->almacenista)
        ->from(route('productos.create'))
        ->post(route('productos.store'), datosIngresoMultiple([
            'numeros_serie' => ['QR-OLD-001', 'QR-OLD-001'],
        ]));

    $this->actingAs($this->almacenista)
        ->get(route('productos.create'))
        ->assertOk()
        ->assertSee('QR-OLD-001')
        ->assertSee('está repetida en la lista escaneada', false);
});
