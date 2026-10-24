<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('gestionar-inventario');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'precio' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'string', 'in:disponible,consumido,averiado,en_reparacion'],
            'condicion' => ['required', 'string', 'in:nuevo,usado'],
            'id_cat' => ['required', 'exists:categorias,id_cat'],
            'id_marca' => ['nullable', 'exists:marcas,id_marca'],
            'id_modelo' => ['nullable', 'exists:modelos,id_modelo'],
            'id_almacen' => ['required', 'exists:almacenes,id_almacen'],
            'id_proveedor' => ['required', 'exists:proveedores,id_prov'],
            'url_img' => ['nullable', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'in:unitario,lote'],
            'cantidad' => ['nullable', 'integer', 'min:1'],
            'cantidad_disponible' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
