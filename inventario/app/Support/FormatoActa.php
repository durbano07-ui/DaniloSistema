<?php

namespace App\Support;

/**
 * Datos fijos del Acta de Entrega (salida con factura), editables solo por el Administrador.
 */
class FormatoActa
{
    public const CAMPOS = [
        'empresa' => 'Empresa',
        'ciudad' => 'Ciudad',
        'entrega_nombre' => 'Entrega – nombre',
        'entrega_cargo' => 'Entrega – cargo',
        'entrega_cedula' => 'Entrega – cédula',
        'entrega_ci_firma' => 'Entrega – C.I./RUC para firma',
        'recibe_nombre' => 'Recibe – nombre',
        'recibe_cargo' => 'Recibe – cargo',
        'recibe_cedula' => 'Recibe – cédula',
        'destino' => 'Destino',
    ];

    private static function ruta(): string
    {
        return storage_path('app/formato_acta.json');
    }

    public static function defaults(): array
    {
        return [
            'empresa' => 'Signal Internet',
            'ciudad' => 'Guaranda',
            'entrega_nombre' => 'Lucia Del Socorro Urbano Urbano',
            'entrega_cargo' => 'Gerente General',
            'entrega_cedula' => '0201657897',
            'entrega_ci_firma' => '0201657897001',
            'recibe_nombre' => 'Juan Carlos Paguay',
            'recibe_cargo' => 'Técnico',
            'recibe_cedula' => '1717486532',
            'destino' => 'FIBRA SAN MIGUEL',
            'iva' => 0.15,
        ];
    }

    public static function get(): array
    {
        $datos = [];
        if (file_exists(self::ruta())) {
            $json = json_decode(file_get_contents(self::ruta()), true);
            if (is_array($json)) {
                $datos = $json;
            }
        }

        return array_merge(self::defaults(), $datos);
    }

    public static function guardar(array $datos): void
    {
        file_put_contents(self::ruta(), json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
