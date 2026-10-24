<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id_cat';

    protected $fillable = ['nombre', 'stock'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_cat', 'id_cat');
    }

    public function marcas()
    {
        return $this->hasMany(Marca::class, 'id_cat', 'id_cat');
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_cat', 'id_cat');
    }
}
