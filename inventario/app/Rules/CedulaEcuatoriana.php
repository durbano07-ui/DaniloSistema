<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CedulaEcuatoriana implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^\d{10}$/', $value)) {
            $fail('El campo debe tener 10 dígitos');
            return;
        }
        $provincia = (int) substr($value, 0, 2);
        if ($provincia < 1 || $provincia > 24) {
            $fail('La provincia es invalida');
            return;
        }
        $tercerDigito = (int) substr($value, 2, 1);
        if ($tercerDigito >= 6) {
            $fail('El tercer digito es invalido');
            return;
        }
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++){
            $digito = (int) $value[$i];
            $producto = $digito * $coeficientes[$i];
            if ($producto >= 10) {
                $producto -= 9; 
            }
            $suma += $producto;
        }

        $digitoVerificador = (int) $value[9];
        $residuo = $suma % 10;
        $verificadorEsperado = $residuo === 0 ? 0 : 10 - $residuo;
        
        if ($verificadorEsperado !== $digitoVerificador) {
            $fail('El digito verificador es invalido');
        }
    }
}
