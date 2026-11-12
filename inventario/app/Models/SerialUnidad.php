<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Serial individual de una unidad perteneciente a un lote del inventario.
 */
class SerialUnidad extends Model
{
    protected $table = 'seriales_unidad';

    protected $primaryKey = 'id_serial';

    protected $fillable = ['numero_serie', 'serial'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'numero_serie', 'numero_serie');
    }
}
