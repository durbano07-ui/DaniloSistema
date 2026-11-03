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

    /**
     * Recalcula el stock guardado de todas las categorías a partir de los productos disponibles
     * (1 por equipo unitario, la cantidad disponible por lote).
     *
     * @return int Número de categorías cuyo stock estaba descuadrado y se corrigió
     */
    public static function recalcularStock(): int
    {
        $reales = Producto::query()
            ->where('estado', 'disponible')
            ->selectRaw("id_cat, SUM(CASE WHEN tipo = 'lote' THEN cantidad_disponible ELSE 1 END) AS total")
            ->groupBy('id_cat')
            ->pluck('total', 'id_cat');

        $corregidas = 0;
        foreach (static::all() as $categoria) {
            $real = (int) ($reales[$categoria->id_cat] ?? 0);
            if ((int) $categoria->stock !== $real) {
                $categoria->update(['stock' => $real]);
                $corregidas++;
            }
        }

        return $corregidas;
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_cat', 'id_cat');
    }
}
