<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $export->codigo() }} — {{ $reporte->tipo }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1E293B; }
        .encabezado { border-bottom: 2px solid #1E293B; padding-bottom: 10px; margin-bottom: 16px; }
        .encabezado td { vertical-align: top; }
        .empresa { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .departamento { font-size: 9px; font-weight: bold; color: #64748B; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }
        .ubicacion { font-size: 9px; color: #94A3B8; margin-top: 2px; }
        .codigo { display: inline-block; background: #1E293B; color: #FFFFFF; font-weight: bold; padding: 3px 8px; border-radius: 4px; }
        .fecha { color: #64748B; margin-top: 6px; }
        .info { width: 100%; background: #F8FAFC; border: 1px solid #E2E8F0; margin-bottom: 18px; }
        .info td { padding: 8px 10px; }
        .etiqueta { font-size: 8px; font-weight: bold; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .titulo-seccion { font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 14px 0 6px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.datos th { background: #1E293B; color: #FFFFFF; font-size: 8px; text-transform: uppercase; text-align: left; padding: 6px; }
        table.datos td { border-bottom: 1px solid #E2E8F0; padding: 5px 6px; }
        table.datos tr.total td { background: #F8FAFC; font-weight: bold; border-top: 2px solid #E2E8F0; }
        .derecha { text-align: right; }
        .vacio { text-align: center; color: #64748B; padding: 10px; }
        .nota { margin-top: 24px; padding-top: 10px; border-top: 1px solid #E2E8F0; font-size: 8px; color: #64748B; font-style: italic; text-align: center; }
    </style>
</head>
<body>
    <table class="encabezado" width="100%">
        <tr>
            <td>
                <div class="empresa">{{ $formato['empresa'] }}</div>
                <div class="departamento">{{ $formato['departamento'] }}</div>
                <div class="ubicacion">{{ $formato['ubicacion'] }}</div>
            </td>
            <td class="derecha">
                <span class="codigo">#{{ $export->codigo() }}</span>
                <div class="fecha">Fecha: <strong>{{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}</strong></div>
            </td>
        </tr>
    </table>

    <table class="info">
        <tr>
            <td><span class="etiqueta">Tipo de informe</span>{{ $reporte->tipo }}</td>
            <td><span class="etiqueta">Emitido por</span>{{ $reporte->usuario?->nombre }}</td>
            <td><span class="etiqueta">Rol / Cédula</span>{{ $reporte->usuario?->rol?->nombre }} ({{ $reporte->numero_cedula }})</td>
        </tr>
    </table>

    @forelse ($export->secciones() as $seccion)
        @if ($seccion['titulo'])
            <div class="titulo-seccion">{{ $seccion['titulo'] }}</div>
        @endif
        @php $ultima = count($seccion['encabezados']) - 1; @endphp
        <table class="datos">
            <thead>
                <tr>
                    @foreach ($seccion['encabezados'] as $i => $encabezado)
                        <th class="{{ is_int($seccion['filas'][0][$i] ?? null) || is_float($seccion['filas'][0][$i] ?? null) ? 'derecha' : '' }}">{{ $encabezado }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($seccion['filas'] as $fila)
                    <tr>
                        @foreach ($fila as $valor)
                            <td class="{{ is_int($valor) || is_float($valor) ? 'derecha' : '' }}">{{ is_float($valor) ? number_format($valor, 2) : $valor }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($seccion['encabezados']) }}" class="vacio">Sin registros.</td>
                    </tr>
                @endforelse
                @if ($seccion['total'])
                    <tr class="total">
                        <td colspan="{{ $ultima }}">{{ $seccion['total'][0] }}</td>
                        <td class="derecha">{{ is_float($seccion['total'][1]) ? number_format($seccion['total'][1], 2) : $seccion['total'][1] }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @empty
        <p class="vacio">Sin registros.</p>
    @endforelse

    <div class="nota">{{ $formato['nota_legal'] }}</div>
</body>
</html>
