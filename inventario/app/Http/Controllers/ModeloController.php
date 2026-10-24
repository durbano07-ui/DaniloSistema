<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ModeloController extends Controller
{
    /**
     * Guarda un nuevo modelo vinculado a una marca y categoría.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'id_cat' => ['required', 'exists:categorias,id_cat'],
            'id_marca' => ['required', 'exists:marcas,id_marca'],
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $nombre = trim($validated['nombre']);

        // Evitar duplicados por marca
        $existe = Modelo::where('id_marca', $validated['id_marca'])
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->first();

        if ($existe) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un modelo con este nombre para la marca seleccionada.',
                ], 422);
            }
            return back()->with('error', 'Ya existe un modelo con ese nombre para la marca seleccionada.');
        }

        $modelo = Modelo::create([
            'id_cat' => $validated['id_cat'],
            'id_marca' => $validated['id_marca'],
            'nombre' => $nombre,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Modelo creado exitosamente.',
                'modelo' => $modelo,
            ]);
        }

        return back()->with('status', 'Modelo creado exitosamente.');
    }

    /**
     * Actualiza el nombre de un modelo.
     */
    public function update(Request $request, Modelo $modelo): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $nombre = trim($validated['nombre']);

        $existe = Modelo::where('id_marca', $modelo->id_marca)
            ->where('id_modelo', '!=', $modelo->id_modelo)
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->first();

        if ($existe) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro modelo con este nombre para esta marca.',
                ], 422);
            }
            return back()->with('error', 'Ya existe otro modelo con ese nombre para esta marca.');
        }

        $modelo->update(['nombre' => $nombre]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Modelo actualizado exitosamente.',
                'modelo' => $modelo,
            ]);
        }

        return back()->with('status', 'Modelo actualizado exitosamente.');
    }

    /**
     * Elimina un modelo si no tiene productos asociados.
     */
    public function destroy(Modelo $modelo): RedirectResponse|JsonResponse
    {
        if ($modelo->productos()->exists()) {
            $msg = 'No se puede eliminar el modelo porque tiene productos registrados en inventario.';
            return request()->wantsJson() || request()->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $modelo->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Modelo eliminado exitosamente.',
            ]);
        }

        return back()->with('status', 'Modelo eliminado exitosamente.');
    }
}
