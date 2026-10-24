<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Modelo;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategoriaController extends Controller
{
    /**
     * Muestra el listado de categorías, marcas y modelos para su gestión.
     */
    public function index(): View
    {
        $categorias = Categoria::with([
            'marcas' => fn($q) => $q->with(['modelos', 'productos'])->withCount('modelos'),
        ])
        ->withCount('productos')
        ->orderBy('nombre')
        ->get();

        return view('categorias.index', compact('categorias'));
    }

    /**
     * Muestra el formulario para registrar una nueva categoría.
     */
    public function create(): View
    {
        return view('categorias.create');
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     */
    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        Categoria::create([
            'nombre' => $request->validated('nombre'),
            'stock' => 0,
        ]);

        return redirect()->route('categorias.index')
            ->with('status', 'Categoría creada exitosamente.');
    }

    /**
     * Muestra el formulario de edición de la categoría.
     */
    public function edit(Categoria $categoria): View
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Actualiza la categoría especificada.
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update([
            'nombre' => $request->validated('nombre'),
        ]);

        return redirect()->route('categorias.index')
            ->with('status', 'Categoría actualizada exitosamente.');
    }

    /**
     * Elimina una categoría si no tiene productos asociados.
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        $this->authorize('gestionar-inventario');

        if ($categoria->productos()->exists() || $categoria->stock > 0) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar: esta categoría todavía tiene productos asociados o stock registrado.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('status', 'Categoría eliminada exitosamente.');
    }
}
