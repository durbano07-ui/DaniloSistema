<?php

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required_without:numero_cedula', 'nullable', 'email', 'max:255'],
            'numero_cedula' => ['required_without:email', 'nullable', 'string', 'max:10'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $field = $this->filled('email') ? 'email' : 'numero_cedula';
        $value = (string) $this->input($field, '');

        if ($field === 'email') {
            if (! Auth::attempt(['email' => $value, 'password' => $this->input('password')], $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            $user = Auth::user();
            if ($user && method_exists($user, 'getAttribute') && $user->getAttribute('estado') !== null && ! $user->estado) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Usuario inactivo',
                ]);
            }
        } else {
            if (! Auth::attempt(['numero_cedula' => $value, 'password' => $this->input('password')], $this->boolean('remember'))) {
                $usuario = Usuario::query()->where('numero_cedula', $value)->first();

                if (! $usuario || ! Hash::check($this->input('password'), $usuario->password)) {
                    RateLimiter::hit($this->throttleKey());

                    throw ValidationException::withMessages([
                        'numero_cedula' => trans('auth.failed'),
                    ]);
                }

                if ($usuario->estado !== null && ! $usuario->estado) {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'numero_cedula' => 'Usuario inactivo',
                    ]);
                }

                Auth::guard('web')->setUser($usuario);
            }

            $usuario = Auth::user();
            if ($usuario && $usuario instanceof Usuario && $usuario->estado !== null && ! $usuario->estado) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'numero_cedula' => 'Usuario inactivo',
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'numero_cedula' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = $this->filled('email') ? $this->string('email') : $this->string('numero_cedula');

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
