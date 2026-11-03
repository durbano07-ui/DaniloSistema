<?php

namespace App\Support;

use App\Models\Modelo;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Busca en internet (Google Imágenes vía SerpApi) una foto del modelo de un equipo,
 * la reduce y la guarda comprimida en WebP para que ocupe poco espacio.
 */
class ImagenModelo
{
    public const ANCHO_MAXIMO = 600;

    public const CALIDAD_WEBP = 75;

    /** Resultados de la búsqueda que se prueban antes de rendirse. */
    private const RESULTADOS_A_PROBAR = 6;

    private const MAX_BYTES_DESCARGA = 8 * 1024 * 1024;

    /** Protege la memoria del servidor frente a imágenes gigantes (25 megapíxeles). */
    private const MAX_PIXELES = 25_000_000;

    public static function disponible(): bool
    {
        return filled(config('services.serpapi.key')) && function_exists('imagewebp');
    }

    /**
     * Programa la búsqueda para después de enviar la respuesta, así el usuario no espera.
     * Solo busca una vez por modelo para no gastar el cupo de la API.
     */
    public static function buscarSiFalta(?Modelo $modelo): void
    {
        if (! $modelo || $modelo->url_img || $modelo->imagen_buscada_at || ! self::disponible()) {
            return;
        }

        app()->terminating(fn () => self::buscar($modelo));
    }

    /**
     * Busca y guarda la imagen del modelo. Con $desde > 0 se salta los primeros resultados
     * (sirve para "probar otra imagen" cuando la primera no corresponde).
     */
    public static function buscar(Modelo $modelo, int $desde = 0): bool
    {
        if (! self::disponible()) {
            return false;
        }

        $modelo->loadMissing('marca');
        $consulta = trim(($modelo->marca?->nombre ?? '').' '.$modelo->nombre);
        $modelo->forceFill(['imagen_buscada_at' => now()])->save();

        try {
            $respuesta = Http::timeout(20)->get('https://serpapi.com/search.json', [
                'engine' => 'google_images',
                'q' => $consulta,
                'api_key' => config('services.serpapi.key'),
            ]);
        } catch (ConnectionException $e) {
            Log::warning("No se pudo consultar SerpApi para «{$consulta}»: {$e->getMessage()}");

            return false;
        }

        if ($respuesta->failed()) {
            Log::warning("SerpApi respondió {$respuesta->status()} para «{$consulta}»: ".$respuesta->json('error'));

            return false;
        }

        $resultados = array_slice($respuesta->json('images_results') ?? [], $desde, self::RESULTADOS_A_PROBAR, true);

        foreach ($resultados as $indice => $resultado) {
            foreach ([$resultado['original'] ?? null, $resultado['thumbnail'] ?? null] as $url) {
                $webp = $url ? self::comprimir(self::descargar($url)) : null;
                if ($webp !== null) {
                    self::guardar($modelo, $webp, (int) $indice);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Reduce la imagen a un ancho máximo y la convierte a WebP. Devuelve null si no es una imagen válida.
     */
    public static function comprimir(?string $contenido): ?string
    {
        if (! $contenido) {
            return null;
        }

        $info = @getimagesizefromstring($contenido);
        if (! $info || $info[0] * $info[1] > self::MAX_PIXELES) {
            return null;
        }

        $imagen = @imagecreatefromstring($contenido);
        if ($imagen === false) {
            return null;
        }

        imagepalettetotruecolor($imagen);
        if (imagesx($imagen) > self::ANCHO_MAXIMO) {
            $imagen = imagescale($imagen, self::ANCHO_MAXIMO);
        }
        imagealphablending($imagen, false);
        imagesavealpha($imagen, true);

        ob_start();
        imagewebp($imagen, null, self::CALIDAD_WEBP);

        return ob_get_clean() ?: null;
    }

    public static function eliminar(Modelo $modelo): void
    {
        if ($modelo->url_img) {
            Storage::disk('local')->delete($modelo->url_img);
        }
    }

    private static function descargar(string $url): ?string
    {
        if (! preg_match('#^https?://#i', $url)) {
            return null;
        }

        try {
            $respuesta = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; InventarioBot/1.0)'])
                ->get($url);
        } catch (ConnectionException) {
            return null;
        }

        if ($respuesta->failed() || strlen($respuesta->body()) > self::MAX_BYTES_DESCARGA) {
            return null;
        }

        return $respuesta->body();
    }

    private static function guardar(Modelo $modelo, string $webp, int $indice): void
    {
        self::eliminar($modelo);

        $ruta = "modelos/{$modelo->id_modelo}-".now()->timestamp.'.webp';
        Storage::disk('local')->put($ruta, $webp);

        $modelo->forceFill(['url_img' => $ruta, 'imagen_indice' => $indice])->save();
    }
}
