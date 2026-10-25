@php
    $existencias = \App\Support\ReporteExport::existencias($productos);
@endphp
<div class="rounded-xl border border-ink-200 overflow-hidden">
    <table class="w-full text-left text-xs divide-y divide-ink-200">
        <thead class="bg-ink-50 font-bold text-ink-500 uppercase text-[10px] tracking-wider">
            <tr>
                <th class="py-2.5 px-3">Nombre del Producto</th>
                <th class="py-2.5 px-3">Modelo</th>
                <th class="py-2.5 px-3">Categoría</th>
                <th class="py-2.5 px-3 text-right">Stock en Bodega</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-ink-100 font-medium">
            @forelse ($existencias as $item)
                <tr>
                    <td class="py-2.5 px-3 font-bold text-ink-900">{{ $item['nombre'] }}</td>
                    <td class="py-2.5 px-3 text-ink-500">{{ $item['modelo'] ?? '—' }}</td>
                    <td class="py-2.5 px-3 text-ink-500">{{ $item['categoria'] ?? '—' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono">{{ $item['stock'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 text-center text-ink-500">No se adjuntaron productos.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-ink-50 font-bold border-t-2 border-ink-200">
            <tr>
                <td colspan="3" class="py-2.5 px-3 text-right uppercase">Total en Stock:</td>
                <td class="py-2.5 px-3 text-right font-mono text-brand-600">{{ $existencias->sum('stock') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
