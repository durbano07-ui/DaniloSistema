<?php

use App\Models\Proveedor;
use App\Models\Usuario;

beforeEach(function () {
    if (! Usuario::where('numero_cedula', '0500000003')->exists()) {
        $this->seed();
    }

    $this->almacenista = Usuario::findOrFail('0500000002');
    $this->tecnico = Usuario::findOrFail('0500000003');
});

test('el almacenista puede registrar un proveedor con correo y teléfono', function () {
    $this->actingAs($this->almacenista)
        ->post(route('proveedores.store'), [
            'nombre' => 'Distribuidora Fibra Andina',
            'correo' => 'ventas@fibraandina.ec',
            'telefono' => '0991234567',
        ])
        ->assertRedirect(route('proveedores.index'));

    $proveedor = Proveedor::where('nombre', 'Distribuidora Fibra Andina')->firstOrFail();

    expect($proveedor->numero_cedula)->toBe($this->almacenista->numero_cedula)
        ->and($proveedor->emails()->value('correo'))->toBe('ventas@fibraandina.ec')
        ->and($proveedor->telefonos()->value('numero'))->toBe('0991234567');

    $this->actingAs($this->almacenista)
        ->get(route('proveedores.index'))
        ->assertOk()
        ->assertSee('Distribuidora Fibra Andina');
});

test('el almacenista puede editar un proveedor y quitar su teléfono', function () {
    $proveedor = Proveedor::create(['nombre' => 'Proveedor Antiguo', 'numero_cedula' => $this->almacenista->numero_cedula]);
    $proveedor->telefonos()->create(['numero' => '022345678']);

    $this->actingAs($this->almacenista)
        ->put(route('proveedores.update', $proveedor), [
            'nombre' => 'Proveedor Renombrado',
            'correo' => 'contacto@renombrado.ec',
            'telefono' => '',
        ])
        ->assertRedirect(route('proveedores.index'));

    $proveedor->refresh();

    expect($proveedor->nombre)->toBe('Proveedor Renombrado')
        ->and($proveedor->emails()->value('correo'))->toBe('contacto@renombrado.ec')
        ->and($proveedor->telefonos()->exists())->toBeFalse();
});

test('no se permiten proveedores con nombre repetido', function () {
    Proveedor::create(['nombre' => 'Proveedor Único', 'numero_cedula' => $this->almacenista->numero_cedula]);

    $this->actingAs($this->almacenista)
        ->post(route('proveedores.store'), ['nombre' => 'Proveedor Único'])
        ->assertSessionHasErrors('nombre');
});

test('el técnico no puede gestionar proveedores', function () {
    $this->actingAs($this->tecnico)->get(route('proveedores.index'))->assertForbidden();
    $this->actingAs($this->tecnico)
        ->post(route('proveedores.store'), ['nombre' => 'Intento Técnico'])
        ->assertForbidden();
});

test('el menú del almacenista muestra proveedores y técnicos', function () {
    $this->actingAs($this->almacenista)
        ->get(route('productos.index'))
        ->assertSee(route('proveedores.index'))
        ->assertSee(route('usuarios.index'));
});
