@php
    $meses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
    $fecha = \Carbon\Carbon::parse($orden->fecha);
    $dinero = fn ($v) => number_format($v, 2, '.', ',');
@endphp

<style>
    .acta { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10.5px; color: #111111; line-height: 1.4; }
    .acta table { width: 100%; border-collapse: collapse; }
    .acta .orden-box { width: 170px; border: 1px solid #111111; text-align: center; }
    .acta .orden-box td { border: 1px solid #111111; padding: 4px; }
    .acta .orden-box .etiqueta { font-weight: bold; font-size: 10px; }
    .acta .orden-box .numero { font-weight: bold; font-size: 13px; height: 18px; }
    .acta .titulo { text-align: center; font-weight: bold; font-size: 14px; margin: 22px 0 18px; }
    .acta .parrafo { text-align: justify; margin: 0 0 14px; }
    .acta .destino { background: #F2F2F2; text-align: center; font-weight: bold; padding: 4px; margin-top: 18px; }
    .acta .fila-factura td { padding: 4px 2px; }
    .acta .detalle th { background: #1F3864; color: #FFFFFF; font-weight: bold; padding: 5px 6px; border: 1px solid #1F3864; font-size: 10px; }
    .acta .detalle td { border: 1px solid #999999; padding: 4px 6px; }
    .acta .detalle td.vacia { height: 14px; }
    .acta .num { text-align: right; white-space: nowrap; }
    .acta .centro { text-align: center; }
    .acta .totales td { padding: 3px 6px; }
    .acta .totales .etiqueta { text-align: right; font-weight: bold; border: 1px solid #999999; width: 90px; }
    .acta .totales .valor { text-align: right; font-weight: bold; border: 1px solid #999999; width: 90px; }
    .acta .firmas { margin-top: 70px; }
    .acta .firmas td { width: 50%; text-align: center; vertical-align: top; font-size: 10px; }
    .acta .firmas .linea { margin-bottom: 4px; }
</style>

<div class="acta">
    <!-- Logo y Número de Orden -->
    <table>
        <tr>
            <td style="vertical-align: top;">
                <img src="{{ $logo }}" alt="{{ $datos['empresa'] }}" style="height: 64px;">
            </td>
            <td style="vertical-align: top;" align="right">
                <table class="orden-box" align="right">
                    <tr><td class="etiqueta">Orden Nº</td></tr>
                    <tr><td class="numero">{{ str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="titulo">Acta de entrega Recepción de la Empresa {{ $datos['empresa'] }}</div>

    <p class="parrafo">
        En la ciudad de {{ $datos['ciudad'] }}, a los {{ $fecha->format('d') }} del mes de {{ $meses[$fecha->month - 1] }} del año {{ $fecha->year }} realizo la entrega de los equipos de telecomunicaciones.
    </p>

    <p class="parrafo">
        Yo, {{ $datos['entrega_nombre'] }} con número de cédula {{ $datos['entrega_cedula'] }}, en calidad de {{ $datos['entrega_cargo'] }}, mediante el presente documento se realiza la entrega formal de los siguientes equipos a/al Sr./Sra. {{ $datos['recibe_nombre'] }} con número de cédula {{ $datos['recibe_cedula'] }}.
    </p>

    <div class="destino">{{ $datos['destino'] }}</div>

    <table class="fila-factura">
        <tr>
            <td>Factura: <strong>{{ $orden->factura }}</strong></td>
            <td align="right">Fecha: <strong>{{ $fecha->format('d/m/Y') }}</strong></td>
        </tr>
    </table>

    <!-- Detalle de Equipos -->
    <table class="detalle">
        <thead>
            <tr>
                <th>Descripción</th>
                <th style="width: 60px;">Cantidad</th>
                <th style="width: 80px;">Precio U.</th>
                <th style="width: 90px;">Precio Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lineas as $linea)
                <tr>
                    <td>{{ $linea['descripcion'] }}</td>
                    <td class="centro">{{ $linea['cantidad'] }}</td>
                    <td class="num">{{ $dinero($linea['precio']) }}</td>
                    <td class="num">{{ $dinero($linea['total']) }}</td>
                </tr>
            @endforeach
            @for ($i = $lineas->count(); $i < 5; $i++)
                <tr><td class="vacia"></td><td></td><td></td><td></td></tr>
            @endfor
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td></td>
            <td class="etiqueta">Subtotal</td>
            <td class="valor">{{ $dinero($subtotal) }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="etiqueta">IVA {{ rtrim(rtrim(number_format($iva * 100, 2), '0'), '.') }}%</td>
            <td class="valor">{{ $dinero($montoIva) }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="etiqueta">TOTAL</td>
            <td class="valor">{{ $dinero($total) }}</td>
        </tr>
    </table>

    <p class="parrafo" style="margin-top: 18px;">Para constancia de la entrega, firman las personas antes mencionadas</p>

    <p class="parrafo"><strong>Nota:</strong> {{ $orden->nota }}</p>

    <!-- Firmas -->
    <table class="firmas">
        <tr>
            <td>
                <div class="linea">F) ……………………………………</div>
                <div>{{ $datos['entrega_nombre'] }}</div>
                <div>{{ $datos['entrega_cargo'] }}</div>
                <div>C.I. {{ $datos['entrega_ci_firma'] }}</div>
                <div><strong>Entrega</strong></div>
            </td>
            <td>
                <div class="linea">F) ……………………………………</div>
                <div>Nombre: {{ $datos['recibe_nombre'] }}</div>
                <div>Cargo: {{ $datos['recibe_cargo'] }}</div>
                <div>C.I. {{ $datos['recibe_cedula'] }}</div>
                <div><strong>Recibe</strong></div>
            </td>
        </tr>
    </table>
</div>
