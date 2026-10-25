<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gestionar-inventario');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', Rule::unique('proveedores', 'nombre')->ignore($this->route('proveedor'), 'id_prov')],
            'correo' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un proveedor con ese nombre.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, +, - y paréntesis.',
        ];
    }
}
