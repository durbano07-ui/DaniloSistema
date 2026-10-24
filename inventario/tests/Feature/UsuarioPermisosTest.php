<?php

use App\Models\Rol;
use App\Models\Usuario;

beforeEach(function () {
    if (! Usuario::where('numero_cedula', '0500000003')->exists()) {
        $this->seed();
    }

    $this->admin = Usuario::findOrFail('0500000001');
    $this->almacenista = Usuario::findOrFail('0500000002');
    $this->tecnico = Usuario::findOrFail('0500000003');
});

test('el almacenista puede registrar un técnico', function () {
    $rolTecnico = Rol::where('nombre', 'Tecnico')->firstOrFail();

    $this->actingAs($this->almacenista)
        ->post(route('usuarios.store'), [
            'numero_cedula' => '1710034065',
            'nombre' => 'Técnico Nuevo',
            'id_rol' => $rolTecnico->id_rol,
        ])
        ->assertRedirect(route('usuarios.index'));

    expect(Usuario::find('1710034065')?->tieneRol('Tecnico'))->toBeTrue();
});

test('el almacenista no puede registrar administradores ni almacenistas', function () {
    $rolAdmin = Rol::where('nombre', 'Administrador')->firstOrFail();

    $this->actingAs($this->almacenista)
        ->post(route('usuarios.store'), [
            'numero_cedula' => '1710034065',
            'nombre' => 'Intento Admin',
            'id_rol' => $rolAdmin->id_rol,
        ])
        ->assertSessionHasErrors('id_rol');

    expect(Usuario::find('1710034065'))->toBeNull();
});

test('el almacenista solo ve y edita técnicos', function () {
    $this->actingAs($this->almacenista)
        ->get(route('usuarios.index'))
        ->assertOk()
        ->assertSee($this->tecnico->nombre)
        ->assertDontSee($this->admin->nombre);

    $this->actingAs($this->almacenista)->get(route('usuarios.edit', $this->tecnico))->assertOk();
    $this->actingAs($this->almacenista)->get(route('usuarios.edit', $this->admin))->assertForbidden();
    $this->actingAs($this->almacenista)
        ->post(route('usuarios.restablecer-password', $this->admin))
        ->assertForbidden();
});

test('el almacenista no puede ascender a un técnico de rol', function () {
    $rolAlmacenista = Rol::where('nombre', 'Almacenista')->firstOrFail();

    $this->actingAs($this->almacenista)
        ->put(route('usuarios.update', $this->tecnico), [
            'nombre' => $this->tecnico->nombre,
            'id_rol' => $rolAlmacenista->id_rol,
        ])
        ->assertSessionHasErrors('id_rol');

    expect($this->tecnico->fresh()->tieneRol('Tecnico'))->toBeTrue();
});

test('solo el administrador puede eliminar usuarios', function () {
    $this->actingAs($this->almacenista)
        ->delete(route('usuarios.destroy', $this->tecnico))
        ->assertForbidden();
});

test('el técnico no puede entrar a la gestión de personal', function () {
    $this->actingAs($this->tecnico)->get(route('usuarios.index'))->assertForbidden();
});
