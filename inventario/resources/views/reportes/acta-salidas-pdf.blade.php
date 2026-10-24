<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de Entrega</title>
    <style>
        @page { margin: 26px 30px 34px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1E293B; }
        table { width: 100%; border-collapse: collapse; }
        .encabezado td { border: 1px solid #3B4A57; vertical-align: middle; }
        .logo { width: 26%; padding: 8px 12px; }
        .titulo { text-align: center; font-size: 15px; font-weight: bold; letter-spacing: 1px; }
        .datos { width: 24%; padding: 0; }
        .datos table td { border: none; border-bottom: 1px solid #3B4A57; padding: 3px 8px; font-size: 8.5px; }
        .datos table tr:last-child td { border-bottom: none; }
        .datos .etiqueta { font-weight: bold; width: 42%; }
        .detalle { margin-top: 10px; }
        .detalle th { background: #3B4A57; color: #FFFFFF; font-size: 8px; text-transform: uppercase; padding: 6px 4px; border: 1px solid #3B4A57; }
        .detalle td { border: 1px solid #94A3B8; padding: 5px 4px; height: 13px; }
        .centro { text-align: center; }
        .notas { font-size: 8px; color: #334155; }
    </style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td class="logo">
                <img src="{{ public_path('images/logo-fibercom.png') }}" alt="fibercom" style="height: 46px;">
            </td>
            <td class="titulo">ACTA DE ENTREGA</td>
            <td class="datos">
                <table>
                    <tr><td class="etiqueta">CÓDIGO:</td><td>{{ $encabezado['codigo'] }}</td></tr>
                    <tr><td class="etiqueta">VERSIÓN:</td><td>{{ $encabezado['version'] }}</td></tr>
                    <tr><td class="etiqueta">FECHA:</td><td>{{ $encabezado['fecha'] }}</td></tr>
                    <tr><td class="etiqueta">PÁGINA:</td><td></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="detalle">
        <thead>
            <tr>
                <th style="width: 3%;">No.</th>
                <th style="width: 7.5%;">Fecha</th>
                <th style="width: 17%;">Descripción</th>
                <th style="width: 4%;">Cant.</th>
                <th style="width: 12%;">Observaciones</th>
                <th style="width: 19%;">Notas</th>
                <th style="width: 10.5%;">Quien recibe</th>
                <th style="width: 7%;">Firma</th>
                <th style="width: 10.5%;">Quien entrega</th>
                <th style="width: 7%;">Firma</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filas as $fila)
                <tr>
                    <td class="centro">{{ $fila[0] }}</td>
                    <td class="centro">{{ $fila[1] }}</td>
                    <td>{{ $fila[2] }}</td>
                    <td class="centro">{{ $fila[3] }}</td>
                    <td>{{ $fila[4] }}</td>
                    <td class="notas">{!! nl2br(e($fila[5])) !!}</td>
                    <td>{{ $fila[6] }}</td>
                    <td></td>
                    <td>{{ $fila[8] }}</td>
                    <td></td>
                </tr>
            @endforeach
            {{-- Completa la hoja con filas vacías numeradas, como el formato impreso (las filas con notas ocupan más espacio) --}}
            @php $relleno = max(0, 22 - $filas->count() - (int) ceil($filas->sum(fn ($f) => substr_count($f[5], "\n") + ($f[5] !== '' ? 1 : 0)) / 2)); @endphp
            @for ($i = $filas->count() + 1; $i <= $filas->count() + $relleno; $i++)
                <tr>
                    <td class="centro" style="color: #94A3B8;">{{ $i }}</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- Número de página: en el recuadro del encabezado (pág. 1) y al pie en las siguientes --}}
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script(function ($pagina, $total, $canvas, $fontMetrics) {
                $fuente = $fontMetrics->getFont('DejaVu Sans');
                $texto = "Página {$pagina} de {$total}";
                if ($pagina === 1) {
                    $canvas->text($canvas->get_width() - 128, 66, $texto, $fuente, 7.5, [0.12, 0.16, 0.23]);
                } else {
                    $canvas->text($canvas->get_width() - 110, $canvas->get_height() - 24, $texto, $fuente, 7.5, [0.4, 0.45, 0.53]);
                }
            });
        }
    </script>
</body>
</html>
