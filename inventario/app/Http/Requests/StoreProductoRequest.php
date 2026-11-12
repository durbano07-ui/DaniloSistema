<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('gestionar-inventario');
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('estado')) {
            $this->merge([
                'estado' => 'disponible',
            ]);
        }

        if (is_array($this->input('numeros_serie'))) {
            $series = collect($this->input('numeros_serie'))
                ->map(fn ($serie) => is_string($serie) ? trim($serie) : $serie)
                ->filter(fn ($serie) => $serie !== null && $serie !== '')
                ->values()
                ->all();

            $this->merge(['numeros_serie' => $series]);

            if (! $this->filled('numero_serie') && count($series) > 0) {
                $this->merge(['numero_serie' => $series[0]]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_serie' => ['required', 'string', 'max:50', 'unique:productos,numero_serie', 'unique:seriales_unidad,serial'],
            'numeros_serie' => ['nullable', 'array', 'max:500', 'prohibited_if:tipo,lote'],
            'numeros_serie.*' => ['required', 'string', 'max:50', 'distinct', 'unique:productos,numero_serie', 'unique:seriales_unidad,serial'],
            'nombre' => ['required', 'string', 'max:255'],
            'precio' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['nullable', 'string', 'in:disponible,consumido,averiado,en_reparacion'],
            'condicion' => ['required', 'string', 'in:nuevo,usado'],
            'id_cat' => ['required', 'exists:categorias,id_cat'],
            'id_marca' => ['nullable', 'exists:marcas,id_marca'],
            'id_modelo' => ['nullable', 'exists:modelos,id_modelo'],
            'id_almacen' => ['required', 'exists:almacenes,id_almacen'],
            'id_proveedor' => ['required', 'exists:proveedores,id_prov'],
            'url_img' => ['nullable', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'in:unitario,lote'],
            'unidad' => ['nullable', 'string', 'in:unidades,metros'],
            'cantidad' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numeros_serie.*.distinct' => 'La serie :input está repetida en la lista escaneada.',
            'numeros_serie.*.unique' => 'La serie :input ya está registrada en el inventario.',
            'numeros_serie.prohibited_if' => 'El ingreso de varias series solo aplica a equipos individuales.',
            'numeros_serie.max' => 'Solo se pueden registrar hasta :max series a la vez.',
        ];
    }
}
