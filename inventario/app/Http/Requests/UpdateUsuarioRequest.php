<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('administrar-usuario', $this->route('usuario'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:30'],
            'id_rol' => ['required', Rule::in(Rol::asignablesPor($this->user())->pluck('id_rol'))],
            'password' => ['nullable', 'string', 'min:8'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
