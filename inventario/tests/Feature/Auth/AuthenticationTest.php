<?php

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $rol = Rol::query()->firstOrCreate(['nombre' => 'Tecnico']);

    $usuario = Usuario::create([
        'numero_cedula' => '0500000011',
        'nombre' => 'Usuario Login',
        'id_rol' => $rol->id_rol,
        'password' => Hash::make('password123'),
        'estado' => true,
    ]);

    $response = $this->post('/login', [
        'numero_cedula' => $usuario->numero_cedula,
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $rol = Rol::query()->firstOrCreate(['nombre' => 'Tecnico']);

    Usuario::create([
        'numero_cedula' => '0500000012',
        'nombre' => 'Usuario Error',
        'id_rol' => $rol->id_rol,
        'password' => Hash::make('password123'),
        'estado' => true,
    ]);

    $this->post('/login', [
        'numero_cedula' => '0500000012',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('inactive usuarios can not authenticate', function () {
    $rol = Rol::query()->firstOrCreate(['nombre' => 'Tecnico']);

    Usuario::create([
        'numero_cedula' => '0500000013',
        'nombre' => 'Usuario Inactivo',
        'id_rol' => $rol->id_rol,
        'password' => Hash::make('password123'),
        'estado' => false,
    ]);

    $this->post('/login', [
        'numero_cedula' => '0500000013',
        'password' => 'password123',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $rol = Rol::query()->firstOrCreate(['nombre' => 'Tecnico']);

    $usuario = Usuario::create([
        'numero_cedula' => '0500000014',
        'nombre' => 'Usuario Logout',
        'id_rol' => $rol->id_rol,
        'password' => Hash::make('password123'),
        'estado' => true,
    ]);

    $response = $this->actingAs($usuario)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
