<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MarcaController extends Controller
{
    /**
     * Guarda una nueva marca vinculada a una categoría.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'id_cat' => ['required', 'exists:categorias,id_cat'],
            'nombre' => ['required', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
        ]);

        $nombre = trim($validated['nombre']);

        // Evitar duplicados por categoría
        $existe = Marca::where('id_cat', $validated['id_cat'])
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->first();

        if ($existe) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una marca con este nombre en la categoría seleccionada.',
                ], 422);
            }
            return back()->with('error', 'Ya existe una marca con ese nombre en la categoría seleccionada.');
        }

        $marca = Marca::create([
            'id_cat' => $validated['id_cat'],
            'nombre' => $nombre,
        ]);

        if (!empty($validated['modelo']) && trim($validated['modelo']) !== '') {
            $modeloNombre = trim($validated['modelo']);
            \App\Models\Modelo::create([
                'id_cat' => $marca->id_cat,
                'id_marca' => $marca->id_marca,
                'nombre' => $modeloNombre,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Marca registrada exitosamente.',
                'marca' => $marca->load('modelos'),
            ]);
        }

        return back()->with('status', 'Marca y modelo registrados exitosamente.');
    }

    /**
     * Actualiza el nombre de una marca.
     */
    public function update(Request $request, Marca $marca): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $nombre = trim($validated['nombre']);

        // Evitar duplicados por categoría
        $existe = Marca::where('id_cat', $marca->id_cat)
            ->where('id_marca', '!=', $marca->id_marca)
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->first();

        if ($existe) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otra marca con este nombre en esta categoría.',
                ], 422);
            }
            return back()->with('error', 'Ya existe otra marca con ese nombre en esta categoría.');
        }

        $marca->update(['nombre' => $nombre]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Marca actualizada exitosamente.',
                'marca' => $marca,
            ]);
        }

        return back()->with('status', 'Marca actualizada exitosamente.');
    }

    /**
     * Elimina una marca si no tiene modelos o productos asociados.
     */
    public function destroy(Marca $marca): RedirectResponse|JsonResponse
    {
        if ($marca->productos()->exists()) {
            $msg = 'No se puede eliminar la marca porque tiene productos registrados en inventario.';
            return request()->wantsJson() || request()->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($marca->modelos()->exists()) {
            $msg = 'No se puede eliminar la marca porque tiene modelos registrados. Elimina o mueve los modelos primero.';
            return request()->wantsJson() || request()->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $marca->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Marca eliminada exitosamente.',
            ]);
        }

        return back()->with('status', 'Marca eliminada exitosamente.');
    }
}
