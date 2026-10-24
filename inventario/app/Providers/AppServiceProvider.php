<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('gestionar-personal', function ($usuario) {
            return $usuario->tieneRol('Administrador') || $usuario->tieneRol('Almacenista');
        });
        // El Almacenista solo puede editar a los Técnicos
        Gate::define('administrar-usuario', function ($usuario, Usuario $objetivo) {
            return $usuario->tieneRol('Administrador')
                || ($usuario->tieneRol('Almacenista') && $objetivo->tieneRol('Tecnico'));
        });
        Gate::define('eliminar-usuario', function ($usuario, Usuario $objetivo) {
            return $usuario->tieneRol('Administrador') && $usuario->numero_cedula !== $objetivo->numero_cedula;
        });
        Gate::define('gestionar-almacen', function ($usuario) {
            return $usuario->tieneRol('Administrador') || $usuario->tieneRol('Almacenista');
        });
        Gate::define('gestionar-inventario', function ($usuario) {
            return $usuario->tieneRol('Administrador') || $usuario->tieneRol('Almacenista');
        });
        Gate::define('generar-reportes', function ($usuario) {
            return $usuario->tieneRol('Administrador') || $usuario->tieneRol('Almacenista');
        });
        Gate::define('enviar-reportes', function ($usuario) {
            return $usuario->tieneRol('Almacenista');
        });
        Gate::define('editar-formato-reporte', function ($usuario) {
            return $usuario->tieneRol('Administrador');
        });
        Gate::define('registrar-consumo', function ($usuario) {
            return $usuario->tieneRol('Tecnico') || $usuario->tieneRol('Almacenista') || $usuario->tieneRol('Administrador');
        });
    }
}
