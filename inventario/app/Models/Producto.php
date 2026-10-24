<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ProductoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ProductoObserver::class)]
class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'numero_serie';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'numero_serie', 'nombre', 'precio', 'url_img', 'estado', 'condicion',
        'numero_cedula', 'id_proveedor', 'id_cat', 'id_marca', 'id_modelo', 'id_almacen',
        'tipo', 'cantidad', 'cantidad_disponible',
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'numero_cedula', 'numero_cedula');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_prov');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_cat', 'id_cat');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'id_almacen', 'id_almacen');
    }

    public function consumos()
    {
        return $this->hasMany(Consumo::class, 'numero_serie', 'numero_serie');
    }

    public function reportes()
    {
        return $this->morphToMany(Reporte::class, 'reportable', 'reportables', 'reportable_id', 'id_reporte');
    }
}