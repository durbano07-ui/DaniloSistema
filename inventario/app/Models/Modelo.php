<?php

namespace App\Models;

use App\Support\ImagenModelo;
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

    protected $casts = [
        'imagen_buscada_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (Modelo $modelo) => ImagenModelo::eliminar($modelo));
    }

    /**
     * URL para mostrar la imagen del modelo (o null si todavía no tiene).
     */
    public function urlImagen(): ?string
    {
        return $this->url_img ? route('modelos.imagen', ['modelo' => $this->id_modelo, 'v' => basename($this->url_img, '.webp')]) : null;
    }

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
