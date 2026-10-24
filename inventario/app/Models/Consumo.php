<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumo extends Model
{
    protected $table = 'consumos';
    protected $primaryKey = 'id_consumo';

    protected $fillable = ['id_orden', 'fecha', 'motivo', 'numero_serie', 'numero_cedula', 'cantidad', 'precio_unitario'];

    public function orden()
    {
        return $this->belongsTo(OrdenSalida::class, 'id_orden', 'id_orden');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'numero_serie', 'numero_serie');
    }

    public function reportes()
    {
        return $this->morphToMany(Reporte::class, 'reportable', 'reportables', 'reportable_id', 'id_reporte');
    }

    public function notas()
    {
        return $this->hasMany(NotaConsumo::class, 'id_consumo', 'id_consumo')->latest('id_nota');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }
}