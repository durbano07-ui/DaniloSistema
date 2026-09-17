<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'almacenes';
    protected $primaryKey = 'id_almacen';

    protected $fillable = ['nombre', 'id_direccion', 'numero_cedula'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'id_direccion', 'id_direccion');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_almacen', 'id_almacen');
    }
}