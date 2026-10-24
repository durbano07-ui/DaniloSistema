<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'numero_cedula';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['numero_cedula', 'nombre', 'id_rol', 'password', 'url_firma', 'estado'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
        'estado' => 'boolean',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'numero_cedula', 'numero_cedula');
    }

    public function almacenes()
    {
        return $this->hasMany(Almacen::class, 'numero_cedula', 'numero_cedula');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'numero_cedula', 'numero_cedula');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'numero_cedula', 'numero_cedula');
    }

    public function consumos()
    {
        return $this->hasMany(Consumo::class, 'numero_cedula', 'numero_cedula');
    }

    public function emails()
    {
        return $this->morphMany(Email::class, 'emailable');
    }

    public function telefonos()
    {
        return $this->morphMany(Telf::class, 'telefonable');
    }

    public function tieneRol(string $nombreRol): bool
    {
        return $this->rol?->nombre === $nombreRol;
    }

    /**
     * Accesor para compatibilidad con componentes que esperan $user->name
     */
    public function getNameAttribute(): string
    {
        return $this->nombre;
    }
}