<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('inventario:recalcular-stock')]
#[Description('Recalcula el stock de cada categoría a partir de los productos disponibles')]
class RecalcularStock extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $corregidas = Categoria::recalcularStock();

        $this->info("Stock recalculado. Categorías corregidas: {$corregidas}.");

        return self::SUCCESS;
    }
}
