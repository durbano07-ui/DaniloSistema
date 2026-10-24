<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $usuarios = Usuario::with('rol')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = Rol::all();

        return view('usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $generada = ! $request->filled('password');
        $data['password'] = $generada ? self::generarPassword() : $request->password;

        $usuario = Usuario::create($data);

        $redirect = redirect()->route('usuarios.index')->with('status', 'Usuario creado exitosamente.');

        // La contraseña generada se muestra una sola vez para entregarla al usuario
        return $generada ? $redirect->with('credenciales', [
            'nombre' => $usuario->nombre,
            'cedula' => $usuario->numero_cedula,
            'password' => $data['password'],
        ]) : $redirect;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usuario $usuario): View
    {
        $roles = Rol::all();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuario $usuario): RedirectResponse
    {
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('status', 'Usuario eliminado exitosamente.');
    }

    /**
     * Genera una nueva contraseña para el usuario y la muestra una sola vez al administrador.
     */
    public function restablecerPassword(Usuario $usuario): RedirectResponse
    {
        $this->authorize('gestionar-personal');

        $password = self::generarPassword();
        $usuario->update(['password' => $password]);

        return redirect()->route('usuarios.index')
            ->with('status', 'Contraseña restablecida exitosamente.')
            ->with('credenciales', [
                'nombre' => $usuario->nombre,
                'cedula' => $usuario->numero_cedula,
                'password' => $password,
            ]);
    }

    /**
     * Contraseña aleatoria de 10 caracteres, sin caracteres que se confunden (0/O, 1/l/I).
     */
    private static function generarPassword(): string
    {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < 10; $i++) {
            $password .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }

        return $password;
    }
}
