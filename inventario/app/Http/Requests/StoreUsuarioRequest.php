<?php

namespace App\Http\Requests;

use App\Models\Rol;
use App\Rules\CedulaEcuatoriana;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('gestionar-personal');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_cedula' => ['required', 'string', 'size:10', 'unique:usuarios,numero_cedula', new CedulaEcuatoriana],
            'nombre' => ['required', 'string', 'max:30'],
            'id_rol' => ['required', Rule::in(Rol::asignablesPor($this->user())->pluck('id_rol'))],
            // Si se deja vacía, el sistema genera una contraseña automáticamente
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}
