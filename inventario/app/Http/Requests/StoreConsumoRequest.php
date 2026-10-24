<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Producto;

class StoreConsumoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('registrar-consumo');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'motivo' => ['required', 'string', 'max:255'],
            'numero_cedula' => ['nullable', 'string', 'exists:usuarios,numero_cedula'],

            // Para pedidos acumulativos (múltiples equipos)
            'items' => ['nullable', 'array', 'min:1', 'required_without:numero_serie'],
            'items.*.numero_serie' => ['required_with:items', 'string', 'exists:productos,numero_serie'],
            'items.*.cantidad' => ['required_with:items', 'integer', 'min:1'],

            // Para retiro individual (compatibilidad previa)
            'numero_serie' => ['nullable', 'string', 'exists:productos,numero_serie', 'required_without:items'],
            'cantidad' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = [];
            if ($this->filled('items') && is_array($this->items)) {
                $items = $this->items;
            } elseif ($this->filled('numero_serie')) {
                $items[] = [
                    'numero_serie' => $this->numero_serie,
                    'cantidad' => max(1, (int) $this->input('cantidad', 1)),
                ];
            }

            foreach ($items as $index => $item) {
                if (empty($item['numero_serie'])) continue;

                $producto = Producto::find($item['numero_serie']);
                if ($producto && $producto->estado !== 'disponible') {
                    $key = isset($item['index']) ? "items.{$index}.numero_serie" : 'numero_serie';
                    $validator->errors()->add(
                        $key,
                        "El equipo {$producto->nombre} ({$producto->numero_serie}) no se puede retirar porque no está disponible (Estado actual: {$producto->estado})."
                    );
                }

                if ($producto && $producto->tipo === 'lote') {
                    $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
                    if ($cantidad > $producto->cantidad_disponible) {
                        $key = isset($item['index']) ? "items.{$index}.cantidad" : 'cantidad';
                        $validator->errors()->add(
                            $key,
                            "La cantidad solicitada ({$cantidad}) para {$producto->nombre} supera el stock disponible ({$producto->cantidad_disponible})."
                        );
                    }
                }
            }
        });
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'numero_serie.required_without' => 'Debes agregar al menos un producto a la salida.',
            'items.required_without' => 'Debes agregar al menos un producto a la salida.',
            'items.min' => 'El pedido debe contener al menos un producto.',
            'numero_serie.exists' => 'El número de serie seleccionado no existe en el catálogo.',
            'motivo.required' => 'Debes especificar el motivo o destino del retiro técnico.',
            'motivo.max' => 'El motivo no puede exceder los 255 caracteres.',
            'fecha.required' => 'La fecha de salida es obligatoria.',
        ];
    }
}
