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

    /**
     * Prefijo de los identificadores asignados al personal registrado sin cédula.
     * Al contener una letra nunca coincide con una cédula real (10 dígitos).
     */
    public const PREFIJO_SIN_CEDULA = 'P';

    /**
     * Genera el siguiente código de usuario (P000001, P000002, ...) para quien no tiene cédula.
     */
    public static function siguienteCodigoSinCedula(): string
    {
        $ultimo = static::query()
            ->where('numero_cedula', 'like', self::PREFIJO_SIN_CEDULA.'%')
            ->pluck('numero_cedula')
            ->map(fn (string $codigo): int => (int) substr($codigo, strlen(self::PREFIJO_SIN_CEDULA)))
            ->max() ?? 0;

        return self::PREFIJO_SIN_CEDULA.str_pad((string) ($ultimo + 1), 6, '0', STR_PAD_LEFT);
    }

    public function tieneCedula(): bool
    {
        return ! str_starts_with($this->numero_cedula, self::PREFIJO_SIN_CEDULA);
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
