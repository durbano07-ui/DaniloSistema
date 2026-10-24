<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Existencias — {{ $fecha->format('d/m/Y') }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1E293B; }
        .encabezado { width: 100%; border-bottom: 2px solid #1E293B; padding-bottom: 10px; margin-bottom: 16px; }
        .encabezado td { vertical-align: top; }
        .empresa { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .departamento { font-size: 9px; font-weight: bold; color: #64748B; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }
        .ubicacion { font-size: 9px; color: #94A3B8; margin-top: 2px; }
        .titulo { font-size: 13px; font-weight: bold; }
        .fecha { color: #64748B; margin-top: 4px; }
        table.datos { width: 100%; border-collapse: collapse; }
        table.datos th { background: #1E293B; color: #FFFFFF; font-size: 8px; text-transform: uppercase; text-align: left; padding: 6px; }
        table.datos td { border-bottom: 1px solid #E2E8F0; padding: 5px 6px; }
        table.datos tr.total td { background: #F8FAFC; font-weight: bold; border-top: 2px solid #E2E8F0; }
        .derecha { text-align: right; }
        .vacio { text-align: center; color: #64748B; padding: 12px; }
    </style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td>
                <div class="empresa">{{ $formato['empresa'] }}</div>
                <div class="departamento">{{ $formato['departamento'] }}</div>
                <div class="ubicacion">{{ $formato['ubicacion'] }}</div>
            </td>
            <td class="derecha">
                <div class="titulo">Reporte de Existencias</div>
                <div class="fecha">{{ $fecha->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <table class="datos">
        <thead>
            <tr>
                @foreach (\App\Support\ReporteExport::COLUMNAS_EXISTENCIAS as $i => $columna)
                    <th class="{{ $i === 5 ? 'derecha' : '' }}">{{ $columna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    @foreach ($fila as $i => $valor)
                        <td class="{{ $i === 5 ? 'derecha' : '' }}">{{ $valor }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">No hay equipos en stock.</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="5">Total en stock</td>
                <td class="derecha">{{ $filas->sum(5) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
