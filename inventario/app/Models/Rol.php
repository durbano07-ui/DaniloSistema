<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    protected $fillable = ['nombre'];

    /**
     * Roles que el usuario puede asignar: el Administrador asigna cualquiera; el Almacenista solo registra Técnicos.
     */
    public static function asignablesPor(Usuario $usuario): Builder
    {
        return static::query()->when(
            ! $usuario->tieneRol('Administrador'),
            fn (Builder $query) => $query->where('nombre', 'Tecnico')
        );
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }
}
