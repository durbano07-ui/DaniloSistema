<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlmacenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gestionar-almacen');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'calle' => ['required', 'string', 'max:150'],
            'id_ciudad' => ['required', 'exists:ciudades,id_ciudad'],
        ];
    }
}
