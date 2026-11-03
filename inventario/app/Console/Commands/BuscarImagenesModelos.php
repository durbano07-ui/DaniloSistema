<?php

namespace App\Console\Commands;

use App\Models\Modelo;
use App\Support\ImagenModelo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('inventario:buscar-imagenes {--limite=20 : Máximo de modelos a procesar (cada uno gasta una búsqueda de SerpApi)} {--reintentar : Incluir modelos en los que ya se buscó sin éxito}')]
#[Description('Busca en internet y guarda comprimida la imagen de los modelos que todavía no tienen una')]
class BuscarImagenesModelos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! ImagenModelo::disponible()) {
            $this->error('Falta configurar SERPAPI_KEY en el .env (o PHP no tiene GD con soporte WebP).');

            return self::FAILURE;
        }

        $modelos = Modelo::with('marca')
            ->whereNull('url_img')
            ->when(! $this->option('reintentar'), fn ($q) => $q->whereNull('imagen_buscada_at'))
            ->limit((int) $this->option('limite'))
            ->get();

        $encontradas = 0;
        foreach ($modelos as $modelo) {
            $ok = ImagenModelo::buscar($modelo);
            $encontradas += $ok ? 1 : 0;
            $this->line(($ok ? '✔ ' : '✘ ').trim(($modelo->marca?->nombre ?? '').' '.$modelo->nombre));
        }

        $this->info("Imágenes guardadas: {$encontradas} de {$modelos->count()}.");

        return self::SUCCESS;
    }
}
