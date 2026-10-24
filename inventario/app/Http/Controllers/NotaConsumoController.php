<?php

namespace App\Http\Controllers;

use App\Models\Consumo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notas de seguimiento del Almacenista sobre cada equipo de una orden de salida.
 */
class NotaConsumoController extends Controller
{
    public function store(Request $request, Consumo $consumo): JsonResponse
    {
        $this->authorize('gestionar-inventario');

        $validated = $request->validate([
            'texto' => ['required', 'string', 'max:1000'],
        ], [
            'texto.required' => 'Escribe la nota antes de guardarla.',
        ]);

        $nota = $consumo->notas()->create([
            'texto' => trim($validated['texto']),
            'numero_cedula' => $request->user()->numero_cedula,
        ]);

        return response()->json(self::formato($nota->load('usuario')), 201);
    }

    public static function formato($nota): array
    {
        return [
            'id' => $nota->id_nota,
            'texto' => $nota->texto,
            'autor' => $nota->usuario?->nombre ?? $nota->numero_cedula,
            'fecha' => $nota->created_at->format('d/m/Y H:i'),
        ];
    }
}
