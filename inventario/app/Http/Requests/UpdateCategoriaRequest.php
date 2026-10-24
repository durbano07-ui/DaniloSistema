<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gestionar-inventario');
    }

    public function rules(): array
    {
        $categoria = $this->route('categoria');
        $categoriaId = is_object($categoria) ? $categoria->id_cat : $categoria;

        return [
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categorias', 'nombre')->ignore($categoriaId, 'id_cat'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres.',
            'nombre.unique' => 'Ya existe otra categoría con este nombre.',
        ];
    }
}
