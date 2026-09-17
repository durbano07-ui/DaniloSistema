<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumo extends Model
{
    protected $table = 'consumos';
    protected $primaryKey = 'id_consumo';

    protected $fillable = ['fecha', 'motivo', 'numero_serie', 'numero_cedula'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'numero_serie', 'numero_serie');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }
}