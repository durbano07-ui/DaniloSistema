<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_prov';

    protected $fillable = ['nombre', 'numero_cedula'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_proveedor', 'id_prov');
    }

    public function emails()
    {
        return $this->morphMany(Email::class, 'emailable');
    }

    public function telefonos()
    {
        return $this->morphMany(Telf::class, 'telefonable');
    }
}