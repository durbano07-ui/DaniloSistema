<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReporteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('generar-reportes');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'in:Inventario General,Existencias por Bodega,Personal y Roles,Salidas y Despachos'],
            'fecha' => ['required', 'date'],
            'id_almacen' => ['nullable', 'exists:almacenes,id_almacen'],
            'numero_cedula_tecnico' => ['nullable', 'exists:usuarios,numero_cedula'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'tipo.required' => 'Debes seleccionar un tipo de reporte.',
            'tipo.in' => 'El tipo de reporte seleccionado no es válido.',
            'fecha.required' => 'La fecha de emisión es obligatoria.',
            'id_almacen.exists' => 'El almacén seleccionado no existe en el sistema.',
        ];
    }
}
