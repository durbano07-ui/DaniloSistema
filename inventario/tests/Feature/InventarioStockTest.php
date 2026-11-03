<?php

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;

beforeEach(function () {
    if (! Usuario::where('numero_cedula', '0500000003')->exists()) {
        $this->seed();
    }

    $this->almacenista = Usuario::findOrFail('0500000002');
    $this->categoria = Categoria::firstOrFail();

    $this->lote = Producto::create([
        'numero_serie' => 'TEST-LOTE-STOCK',
        'nombre' => 'Cable de prueba',
        'tipo' => 'lote',
        'cantidad' => 100,
        'cantidad_disponible' => 40,
        'precio' => 0,
        'estado' => 'disponible',
        'condicion' => 'nuevo',
        'id_cat' => $this->categoria->id_cat,
        'id_almacen' => Almacen::firstOrFail()->id_almacen,
        'id_proveedor' => Proveedor::firstOrFail()->id_prov,
        'numero_cedula' => $this->almacenista->numero_cedula,
    ]);
});

function datosEdicionLote(Producto $lote, array $cambios): array
{
    return array_merge([
        'nombre' => $lote->nombre,
        'estado' => 'disponible',
        'condicion' => 'nuevo',
        'id_cat' => $lote->id_cat,
        'id_almacen' => $lote->id_almacen,
        'id_proveedor' => $lote->id_proveedor,
        'tipo' => 'lote',
        'cantidad' => 100,
    ], $cambios);
}

test('el stock disponible de un lote no puede superar su cantidad total', function () {
    $this->actingAs($this->almacenista)
        ->put(route('productos.update', $this->lote), datosEdicionLote($this->lote, ['cantidad_disponible' => 150]))
        ->assertSessionHasErrors('cantidad_disponible');

    expect($this->lote->fresh()->cantidad_disponible)->toBe(40);

    $this->actingAs($this->almacenista)
        ->put(route('productos.update', $this->lote), datosEdicionLote($this->lote, ['cantidad_disponible' => 100]))
        ->assertSessionHasNoErrors();

    expect($this->lote->fresh()->cantidad_disponible)->toBe(100);
});

test('recalcular stock corrige una categoría descuadrada', function () {
    $correcto = $this->categoria->fresh()->stock;
    $this->categoria->update(['stock' => $correcto + 999]);

    $this->actingAs($this->almacenista)
        ->post(route('categorias.recalcular-stock'))
        ->assertRedirect(route('categorias.index'))
        ->assertSessionHas('status');

    expect($this->categoria->fresh()->stock)->toBe($correcto);
});
