<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('reportes.index') }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Reporte #REP-{{ str_pad($reporte->id_reporte, 4, '0', STR_PAD_LEFT) }}</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-600 border border-brand-200">
                {{ $reporte->tipo }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Action bar (No se imprime) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 rounded-2xl bg-white border border-ink-200 shadow-card print:hidden">
            <div class="text-xs text-ink-500">
                Documento emitido el <strong class="text-ink-900 font-mono">{{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}</strong> por <strong class="text-ink-900">{{ $reporte->usuario?->nombre }}</strong>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Exportar a Excel -->
                <a href="{{ route('reportes.excel', $reporte) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-ink-50 border border-ink-200 hover:border-brand-600 text-ink-900 hover:text-brand-600 text-xs font-bold shadow-xs transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Exportar Excel</span>
                </a>

                <!-- Exportar a PDF -->
                <a href="{{ route('reportes.pdf', $reporte) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Exportar PDF</span>
                </a>
            </div>
        </div>

        <!-- Official Report Sheet -->
        <div class="bg-white text-ink-900 rounded-2xl p-8 sm:p-12 shadow-card border border-ink-200 print:shadow-none print:border-none print:p-0">
            <!-- Header Institucional -->
            <div class="border-b-2 border-ink-900 pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-ink-900 uppercase">
                        {{ $formato['empresa'] }}
                    </h2>
                    <p class="text-xs font-bold text-ink-500 uppercase tracking-widest mt-0.5">
                        {{ $formato['departamento'] }}
                    </p>
                    <p class="text-xs text-ink-400 mt-1">
                        {{ $formato['ubicacion'] }}
                    </p>
                </div>
                <div class="sm:text-right">
                    <span class="inline-block px-3 py-1 bg-ink-900 text-white font-mono text-xs font-bold rounded-lg">
                        #REP-{{ str_pad($reporte->id_reporte, 4, '0', STR_PAD_LEFT) }}
                    </span>
                    <p class="text-xs text-ink-500 mt-1.5 font-medium">
                        Fecha: <strong class="font-mono text-ink-900">{{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}</strong>
                    </p>
                </div>
            </div>

            <!-- Información del Documento -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-5 rounded-xl bg-ink-50 border border-ink-200 text-xs mb-8">
                <div>
                    <span class="block text-ink-500 uppercase font-bold text-[10px] tracking-wider">Tipo de Informe</span>
                    <span class="font-semibold text-brand-600 text-sm block mt-0.5">{{ $reporte->tipo }}</span>
                </div>
                <div>
                    <span class="block text-ink-500 uppercase font-bold text-[10px] tracking-wider">Emitido por</span>
                    <span class="font-bold text-ink-900 block mt-0.5">{{ $reporte->usuario?->nombre ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-ink-500 uppercase font-bold text-[10px] tracking-wider">Rol / Cédula</span>
                    <span class="font-mono text-ink-500 block mt-0.5">{{ $reporte->usuario?->rol?->nombre }} ({{ $reporte->numero_cedula }})</span>
                </div>
            </div>

            <!-- Cuerpo del Reporte según el Tipo -->
            @if ($reporte->tipo === 'Inventario General')
                <div class="mb-8">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-900 mb-3 border-b border-ink-200 pb-2">
                        Detalle de Existencias
                    </h3>
                    @include('reportes._tabla-existencias', ['productos' => $reporte->productos])
                </div>
            @elseif ($reporte->tipo === 'Existencias por Bodega')
                <div class="mb-8 space-y-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-900 mb-3 border-b border-ink-200 pb-2">
                        Detalle de Existencias por Bodega
                    </h3>
                    @forelse ($reporte->almacenes as $alm)
                        <div>
                            <div class="mb-3">
                                <h4 class="font-bold text-sm text-ink-900">{{ $alm->nombre }}</h4>
                                <p class="text-xs text-ink-500">{{ $alm->direccion?->calle ?? 'Sin dirección' }} — {{ $alm->direccion?->ciudad?->nombre }}</p>
                            </div>
                            @include('reportes._tabla-existencias', ['productos' => $alm->productos])
                        </div>
                    @empty
                        <p class="text-xs text-ink-500">No se asociaron almacenes al reporte.</p>
                    @endforelse
                </div>
            @elseif ($reporte->tipo === 'Salidas y Despachos')
                <div class="mb-8 space-y-6">
                    <!-- Tarjetas de Resumen Ejecutivo -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-ink-50 border border-ink-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500 block">Total Equipos Retirados</span>
                            <span class="text-xl font-semibold text-brand-600 mt-1 block font-mono">{{ $reporte->consumos->count() }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-ink-50 border border-ink-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500 block">Técnicos Receptores</span>
                            <span class="text-xl font-semibold text-ink-900 mt-1 block font-mono">{{ $reporte->consumos->pluck('numero_cedula')->unique()->count() }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-ink-50 border border-ink-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500 block">Bodegas de Origen</span>
                            <span class="text-xl font-semibold text-ink-900 mt-1 block font-mono">{{ $reporte->consumos->pluck('producto.id_almacen')->filter()->unique()->count() }}</span>
                        </div>
                    </div>

                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-900 mb-3 border-b border-ink-200 pb-2">
                        Padrón Detallado de Salidas y Vales Técnicos
                    </h3>
                    <div class="rounded-xl border border-ink-200 overflow-hidden">
                        <table class="w-full text-left text-xs divide-y divide-ink-200">
                            <thead class="bg-ink-50 font-bold text-ink-500 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Vale / Fecha</th>
                                    <th class="py-2.5 px-3">Equipo &amp; Serie</th>
                                    <th class="py-2.5 px-3">Técnico Receptor</th>
                                    <th class="py-2.5 px-3">Bodega Origen</th>
                                    <th class="py-2.5 px-3">Motivo / OT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 font-medium">
                                @forelse ($reporte->consumos as $c)
                                    <tr>
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            <span class="font-mono text-xs font-bold text-brand-600 block">
                                                #CNS-{{ str_pad($c->id_consumo, 4, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <span class="text-[11px] text-ink-500 font-mono">
                                                {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="font-bold text-ink-900 block">{{ $c->producto?->nombre ?? 'Equipo' }}</span>
                                            <span class="font-mono text-[11px] text-ink-500">SN: {{ $c->numero_serie }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            <span class="font-semibold text-ink-900 block">{{ $c->usuario?->nombre ?? 'N/A' }}</span>
                                            <span class="font-mono text-[11px] text-ink-500">CI: {{ $c->numero_cedula }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-ink-500 whitespace-nowrap">
                                            {{ $c->producto?->almacen?->nombre ?? 'Central' }}
                                        </td>
                                        <td class="py-2.5 px-3 text-ink-500 text-xs max-w-xs">
                                            <span class="italic line-clamp-2" title="{{ $c->motivo }}">&ldquo;{{ $c->motivo }}&rdquo;</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-ink-500">
                                            No se encontraron salidas técnicas asociadas a los criterios de este reporte.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-ink-50 font-bold border-t-2 border-ink-200">
                                <tr>
                                    <td colspan="3" class="py-2.5 px-3 uppercase text-[10px] text-ink-500">Consolidado Total de Salidas:</td>
                                    <td colspan="2" class="py-2.5 px-3 text-right font-mono text-brand-600">{{ $reporte->consumos->count() }} equipos despachados</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @elseif ($reporte->tipo === 'Salidas con Factura')
                @php
                    $ordenFactura = $reporte->consumos->first()?->orden;
                    $subtotalFactura = $reporte->consumos->sum(fn ($c) => (int) ($c->cantidad ?? 1) * (float) $c->precio_unitario);
                    $ivaFactura = (float) ($ordenFactura?->iva ?? 0);
                    $montoIvaFactura = round($subtotalFactura * $ivaFactura, 2);
                @endphp
                <div class="mb-8 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-ink-50 border border-ink-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500 block">Factura</span>
                            <span class="text-xl font-semibold text-brand-600 mt-1 block font-mono">{{ $ordenFactura?->factura ?? '—' }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-ink-50 border border-ink-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500 block">Equipos</span>
                            <span class="text-xl font-semibold text-ink-900 mt-1 block font-mono">{{ $reporte->consumos->count() }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-ink-50 border border-ink-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500 block">Total con IVA</span>
                            <span class="text-xl font-semibold text-ink-900 mt-1 block font-mono">${{ number_format($subtotalFactura + $montoIvaFactura, 2) }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-ink-200 overflow-hidden">
                        <table class="w-full text-left text-xs divide-y divide-ink-200">
                            <thead class="bg-ink-50 font-bold text-ink-500 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Equipo &amp; Serie</th>
                                    <th class="py-2.5 px-3 text-right">Cantidad</th>
                                    <th class="py-2.5 px-3 text-right">Precio U.</th>
                                    <th class="py-2.5 px-3 text-right">Precio Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 font-medium">
                                @foreach ($reporte->consumos as $c)
                                    <tr>
                                        <td class="py-2.5 px-3">
                                            <span class="font-bold text-ink-900 block">{{ $c->producto?->nombre ?? 'Equipo' }}</span>
                                            <span class="font-mono text-[11px] text-ink-500">SN: {{ $c->numero_serie }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono">{{ $c->cantidad ?? 1 }}{{ $c->producto?->esMetros() ? ' m' : '' }}</td>
                                        <td class="py-2.5 px-3 text-right font-mono">{{ number_format((float) $c->precio_unitario, 2) }}</td>
                                        <td class="py-2.5 px-3 text-right font-mono">{{ number_format((int) ($c->cantidad ?? 1) * (float) $c->precio_unitario, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-ink-50 font-bold border-t-2 border-ink-200">
                                <tr>
                                    <td colspan="3" class="py-2 px-3 text-right uppercase text-[10px] text-ink-500">Subtotal</td>
                                    <td class="py-2 px-3 text-right font-mono">{{ number_format($subtotalFactura, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="py-2 px-3 text-right uppercase text-[10px] text-ink-500">IVA {{ rtrim(rtrim(number_format($ivaFactura * 100, 2), '0'), '.') }}%</td>
                                    <td class="py-2 px-3 text-right font-mono">{{ number_format($montoIvaFactura, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="py-2 px-3 text-right uppercase text-[10px] text-ink-500">Total</td>
                                    <td class="py-2 px-3 text-right font-mono text-brand-600">{{ number_format($subtotalFactura + $montoIvaFactura, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @elseif ($reporte->tipo === 'Personal y Roles')
                <div class="mb-8">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-900 mb-3 border-b border-ink-200 pb-2">
                        Padrón Oficial de Usuarios del Sistema
                    </h3>
                    <div class="rounded-xl border border-ink-200 overflow-hidden">
                        <table class="w-full text-left text-xs divide-y divide-ink-200">
                            <thead class="bg-ink-50 font-bold text-ink-500 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Cédula</th>
                                    <th class="py-2.5 px-3">Nombre</th>
                                    <th class="py-2.5 px-3">Rol</th>
                                    <th class="py-2.5 px-3 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 font-medium">
                                @forelse ($reporte->usuarios as $u)
                                    <tr>
                                        <td class="py-2.5 px-3 font-mono text-ink-500">{{ $u->numero_cedula }}</td>
                                        <td class="py-2.5 px-3 font-bold text-ink-900">{{ $u->nombre }}</td>
                                        <td class="py-2.5 px-3 text-ink-500">{{ $u->rol?->nombre ?? 'N/A' }}</td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $u->estado ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }}">
                                                {{ $u->estado ? 'ACTIVO' : 'INACTIVO' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-ink-500">No se asociaron usuarios.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Cláusula Oficial -->
            <div class="pt-8 border-t border-ink-200">
                <p class="text-[11px] text-ink-500 italic text-center max-w-xl mx-auto leading-relaxed">
                    «{{ $formato['nota_legal'] }}»
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
