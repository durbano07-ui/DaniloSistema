<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
     protected $table = 'ciudades';
    protected $primaryKey = 'id_ciudad';

    protected $fillable = ['nombre', 'provincia'];

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_ciudad', 'id_ciudad');
    }
}
