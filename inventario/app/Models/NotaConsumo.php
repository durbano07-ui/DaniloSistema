<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaConsumo extends Model
{
    protected $table = 'notas_consumo';
    protected $primaryKey = 'id_nota';

    protected $fillable = ['id_consumo', 'numero_cedula', 'texto'];

    public function consumo()
    {
        return $this->belongsTo(Consumo::class, 'id_consumo', 'id_consumo');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }
}
