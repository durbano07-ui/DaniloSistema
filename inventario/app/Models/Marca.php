<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';
    protected $primaryKey = 'id_marca';

    protected $fillable = [
        'nombre',
        'id_cat',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_cat', 'id_cat');
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_marca', 'id_marca');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_marca', 'id_marca');
    }

    public function getStockAcumuladoAttribute(): int
    {
        $unitarios = $this->productos()->where('tipo', 'unitario')->where('estado', 'disponible')->count();
        $lotes = (int) $this->productos()->where('tipo', 'lote')->where('estado', 'disponible')->sum('cantidad_disponible');
        return $unitarios + $lotes;
    }
}
