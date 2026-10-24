<?php

namespace App\Observers;

use App\Models\Producto;
use App\Models\Categoria;

class ProductoObserver
{
    /**
     * Handle the Producto "created" event.
     */
    public function created(Producto $producto): void
    {
        if ($producto->estado === "disponible") {
            $cant = ($producto->tipo === 'lote') ? (int) ($producto->cantidad_disponible ?? $producto->cantidad ?? 1) : 1;
            $this->ajustarStock($producto->id_cat, $cant);
        }
    }

    /**
     * Handle the Producto "updated" event.
     */
    public function updated(Producto $producto): void
    {
        $estabaDisponible = ($producto->getOriginal('estado') === 'disponible');
        $estaDisponible = ($producto->estado === 'disponible');
        $catAnterior = (int) $producto->getOriginal('id_cat');
        $catNueva = (int) $producto->id_cat;

        if ($producto->tipo === 'lote') {
            $dispAnterior = $estabaDisponible ? (int) ($producto->getOriginal('cantidad_disponible') ?? 1) : 0;
            $dispNueva = $estaDisponible ? (int) ($producto->cantidad_disponible ?? 0) : 0;
            $diff = $dispNueva - $dispAnterior;

            if ($catAnterior === $catNueva) {
                if ($diff !== 0) {
                    $this->ajustarStock($catNueva, $diff);
                }
            } else {
                if ($dispAnterior > 0) $this->ajustarStock($catAnterior, -$dispAnterior);
                if ($dispNueva > 0) $this->ajustarStock($catNueva, $dispNueva);
            }
            return;
        }

        // Tipo Unitario (1 por equipo)
        if ($estabaDisponible && ! $estaDisponible) {
            $this->ajustarStock($catAnterior, -1);
        } elseif (! $estabaDisponible && $estaDisponible) {
            $this->ajustarStock($catNueva, 1);
        } elseif ($estabaDisponible && $estaDisponible && $catAnterior !== $catNueva) {
            $this->ajustarStock($catAnterior, -1);
            $this->ajustarStock($catNueva, 1);
        }
    }

    /**
     * Handle the Producto "deleted" event.
     */
    public function deleted(Producto $producto): void
    {
        if ($producto->estado === "disponible") {
            $cant = ($producto->tipo === 'lote') ? (int) ($producto->cantidad_disponible ?? $producto->cantidad ?? 1) : 1;
            $this->ajustarStock($producto->id_cat, -$cant);
        }
    }

    /**
     * Ajustar el stock de una categoría de forma segura.
     */
    public function ajustarStock(int $idCat, int $cantidad): void
    {
        $categoria = Categoria::find($idCat);
        if (! $categoria) {
            return;
        }

        if ($cantidad > 0) {
            $categoria->increment('stock', $cantidad);
        } elseif ($cantidad < 0) {
            $nuevoStock = max(0, $categoria->stock + $cantidad);
            $categoria->update(['stock' => $nuevoStock]);
        }
    }
}
