<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de Entrega — Orden {{ str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 36px 44px; }
        body { margin: 0; }
    </style>
</head>
<body>
    @include('consumos._acta-documento', ['logo' => public_path('images/logo-acta.png')])
</body>
</html>
