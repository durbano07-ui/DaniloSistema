@php
    $existencias = \App\Support\ReporteExport::existencias($productos);
@endphp
<div class="rounded-xl border border-[#E2E8F0] overflow-hidden">
    <table class="w-full text-left text-xs divide-y divide-[#E2E8F0]">
        <thead class="bg-[#F8FAFC] font-bold text-[#64748B] uppercase text-[10px] tracking-wider">
            <tr>
                <th class="py-2.5 px-3">Nombre del Producto</th>
                <th class="py-2.5 px-3">Modelo</th>
                <th class="py-2.5 px-3">Categoría</th>
                <th class="py-2.5 px-3 text-right">Stock en Bodega</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#F1F5F9] font-medium">
            @forelse ($existencias as $item)
                <tr>
                    <td class="py-2.5 px-3 font-bold text-[#1E293B]">{{ $item['nombre'] }}</td>
                    <td class="py-2.5 px-3 text-[#64748B]">{{ $item['modelo'] ?? '—' }}</td>
                    <td class="py-2.5 px-3 text-[#64748B]">{{ $item['categoria'] ?? '—' }}</td>
                    <td class="py-2.5 px-3 text-right font-mono">{{ $item['stock'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 text-center text-[#64748B]">No se adjuntaron productos.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-[#F8FAFC] font-bold border-t-2 border-[#E2E8F0]">
            <tr>
                <td colspan="3" class="py-2.5 px-3 text-right uppercase">Total en Stock:</td>
                <td class="py-2.5 px-3 text-right font-mono text-[#2563EB]">{{ $existencias->sum('stock') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
