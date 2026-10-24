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

    /**
     * Total despachado separando piezas y metros: "3 unidades + 50 m".
     */
    public function resumenCantidades(): string
    {
        [$enMetros, $enUnidades] = $this->consumos->partition(fn ($c) => $c->producto?->esMetros());

        $partes = [];
        if ($enUnidades->isNotEmpty()) {
            $partes[] = Producto::formatearCantidad((int) $enUnidades->sum('cantidad'));
        }
        if ($enMetros->isNotEmpty()) {
            $partes[] = Producto::formatearCantidad((int) $enMetros->sum('cantidad'), 'metros');
        }

        return implode(' + ', $partes);
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
