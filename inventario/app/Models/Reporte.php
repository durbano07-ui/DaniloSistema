<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table = 'reportes';
    protected $primaryKey = 'id_reporte';

    protected $fillable = ['tipo', 'fecha', 'numero_cedula'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }

    public function usuarios()
    {
        return $this->morphedByMany(Usuario::class, 'reportable', 'reportables', 'id_reporte', 'reportable_id');
    }

    public function productos()
    {
        return $this->morphedByMany(Producto::class, 'reportable', 'reportables', 'id_reporte', 'reportable_id');
    }

    public function almacenes()
    {
        return $this->morphedByMany(Almacen::class, 'reportable', 'reportables', 'id_reporte', 'reportable_id');
    }

    public function consumos()
    {
        return $this->morphedByMany(Consumo::class, 'reportable', 'reportables', 'id_reporte', 'reportable_id');
    }
}