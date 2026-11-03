<?php

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\Usuario;
use App\Support\ImagenModelo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    if (! Usuario::where('numero_cedula', '0500000003')->exists()) {
        $this->seed();
    }

    $this->almacenista = Usuario::findOrFail('0500000002');

    Storage::fake('local');
    config(['services.serpapi.key' => 'clave-de-prueba']);

    $categoria = Categoria::firstOrFail();
    $marca = Marca::create(['nombre' => 'MarcaPrueba', 'id_cat' => $categoria->id_cat]);
    $this->modelo = Modelo::create(['nombre' => 'X-100', 'id_marca' => $marca->id_marca, 'id_cat' => $categoria->id_cat]);
});

function pngDePrueba(int $ancho, int $alto): string
{
    $imagen = imagecreatetruecolor($ancho, $alto);
    imagefill($imagen, 0, 0, imagecolorallocate($imagen, 30, 90, 200));
    ob_start();
    imagepng($imagen);

    return ob_get_clean();
}

function fingirSerpApi(): void
{
    Http::fake([
        'serpapi.com/*' => Http::response(['images_results' => [
            ['original' => 'https://ejemplo.test/rota.jpg', 'thumbnail' => 'https://ejemplo.test/tambien-rota.jpg'],
            ['original' => 'https://ejemplo.test/buena.png'],
            ['original' => 'https://ejemplo.test/otra.png'],
        ]]),
        'ejemplo.test/rota.jpg' => Http::response('no es una imagen'),
        'ejemplo.test/tambien-rota.jpg' => Http::response('', 404),
        'ejemplo.test/*' => Http::response(pngDePrueba(1600, 1200), 200, ['Content-Type' => 'image/png']),
    ]);
}

test('busca la imagen del modelo, salta resultados inválidos y la guarda reducida en WebP', function () {
    fingirSerpApi();

    expect(ImagenModelo::buscar($this->modelo))->toBeTrue();

    $this->modelo->refresh();
    Http::assertSent(fn ($request) => str_contains($request->url(), 'serpapi.com') && $request['q'] === 'MarcaPrueba X-100');
    expect($this->modelo->imagen_indice)->toBe(1);
    Storage::disk('local')->assertExists($this->modelo->url_img);

    [$ancho, , $tipo] = getimagesizefromstring(Storage::disk('local')->get($this->modelo->url_img));
    expect($ancho)->toBe(ImagenModelo::ANCHO_MAXIMO)
        ->and($tipo)->toBe(IMAGETYPE_WEBP);

    $this->actingAs($this->almacenista)
        ->get($this->modelo->urlImagen())
        ->assertOk()
        ->assertHeader('Content-Type', 'image/webp');
});

test('"probar otra imagen" usa el siguiente resultado y borra la anterior', function () {
    fingirSerpApi();
    ImagenModelo::buscar($this->modelo);
    $anterior = $this->modelo->fresh()->url_img;

    $this->travel(2)->seconds();

    $this->actingAs($this->almacenista)
        ->from(route('productos.index'))
        ->post(route('modelos.buscar-imagen', $this->modelo), ['siguiente' => 1])
        ->assertSessionHas('status');

    expect($this->modelo->fresh()->imagen_indice)->toBe(2);
    Storage::disk('local')->assertMissing($anterior);
});

test('sin clave de SerpApi no se hace ninguna búsqueda', function () {
    config(['services.serpapi.key' => null]);
    Http::fake();

    ImagenModelo::buscarSiFalta($this->modelo);

    $this->actingAs($this->almacenista)
        ->from(route('productos.index'))
        ->post(route('modelos.buscar-imagen', $this->modelo))
        ->assertSessionHas('error');

    Http::assertNothingSent();
    expect($this->modelo->fresh()->imagen_buscada_at)->toBeNull();
});
