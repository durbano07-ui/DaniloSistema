<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    protected $table = 'modelos';
    protected $primaryKey = 'id_modelo';

    protected $fillable = [
        'nombre',
        'id_marca',
        'id_cat',
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_cat', 'id_cat');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_modelo', 'id_modelo');
    }
}
