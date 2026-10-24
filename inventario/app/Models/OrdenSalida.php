<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenSalida extends Model
{
    protected $table = 'ordenes_salida';
    protected $primaryKey = 'id_orden';

    protected $fillable = ['tipo', 'fecha', 'motivo', 'factura', 'nota', 'iva', 'datos_acta', 'numero_cedula'];

    protected $casts = [
        'datos_acta' => 'array',
        'iva' => 'decimal:4',
    ];

    public function esFactura(): bool
    {
        return $this->tipo === 'factura';
    }

    public function consumos()
    {
        return $this->hasMany(Consumo::class, 'id_orden', 'id_orden');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }
}
