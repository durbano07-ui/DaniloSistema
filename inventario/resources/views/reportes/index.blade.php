<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Gestión de Reportes</h1>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Controls: Filters, Config format and CTA -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @can('editar-formato-reporte')
                    <a href="{{ route('reportes.formato.edit') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-ink-200 bg-white hover:bg-ink-50 text-ink-500 hover:text-ink-900 text-xs font-semibold shadow-xs transition-all">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Formato Institucional</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Filters Form -->
        <div class="p-4 rounded-2xl bg-white border border-ink-200 shadow-card">
            <form method="GET" action="{{ route('reportes.index') }}" class="flex flex-wrap items-center gap-3">
                <div>
                    <select name="tipo"
                            onchange="this.form.submit()"
                            class="rounded-xl bg-ink-50 border border-ink-200 text-ink-900 text-xs py-2 px-3 focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600">
                        <option value="" disabled {{ request('tipo') ? '' : 'selected' }}>Seleccionar Reporte</option>
                        <option value="Existencias" {{ request('tipo') === 'Existencias' ? 'selected' : '' }}>Existencias</option>
                        <option value="Salidas y Despachos" {{ request('tipo') === 'Salidas y Despachos' ? 'selected' : '' }}>Salidas y Despachos</option>
                        <option value="Salidas con Factura" {{ request('tipo') === 'Salidas con Factura' ? 'selected' : '' }}>Salidas con Factura</option>
                    </select>
                </div>

                @if ($reportes !== null)
                <div class="flex items-center gap-2 text-xs text-ink-500 font-semibold">
                    <span>Desde:</span>
                    <input type="date"
                           name="fecha_desde"
                           value="{{ request('fecha_desde') }}"
                           class="rounded-xl bg-ink-50 border border-ink-200 text-ink-900 text-xs py-1.5 px-2.5 focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600" />
                </div>

                <div class="flex items-center gap-2 text-xs text-ink-500 font-semibold">
                    <span>Hasta:</span>
                    <input type="date"
                           name="fecha_hasta"
                           value="{{ request('fecha_hasta') }}"
                           class="rounded-xl bg-ink-50 border border-ink-200 text-ink-900 text-xs py-1.5 px-2.5 focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600" />
                </div>

                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-ink-900 hover:bg-ink-700 text-white text-xs font-bold transition-colors">
                    Filtrar
                </button>

                @if(request('fecha_desde') || request('fecha_hasta'))
                    <a href="{{ route('reportes.index', ['tipo' => request('tipo')]) }}" class="text-xs text-brand-600 hover:underline font-semibold">
                        Limpiar fechas
                    </a>
                @endif
                @endif
            </form>
        </div>

        @if (request('tipo') === 'Existencias')
            <!-- Reporte de Existencias: opciones de visualización -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                <a href="{{ route('reportes.existencias.pdf') }}"
                   class="group p-6 rounded-2xl bg-white border border-ink-200 hover:border-brand-600 shadow-card hover:shadow-elevated transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-base font-semibold text-ink-900 group-hover:text-brand-600 transition-colors">PDF</span>
                        <span class="block text-xs text-ink-500">Ver el reporte en el visor de PDF</span>
                    </div>
                </a>
                <a href="{{ route('reportes.existencias.excel') }}"
                   class="group p-6 rounded-2xl bg-white border border-ink-200 hover:border-brand-600 shadow-card hover:shadow-elevated transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-base font-semibold text-ink-900 group-hover:text-brand-600 transition-colors">Excel</span>
                        <span class="block text-xs text-ink-500">Ver el reporte como hoja de cálculo</span>
                    </div>
                </a>
            </div>
        @elseif (request('tipo') === 'Salidas y Despachos')
            <!-- Acta de Entrega de salidas: rango de fechas y opciones de visualización -->
            <form method="GET" class="space-y-4 max-w-2xl">
                <div class="flex flex-wrap items-center gap-4 p-4 rounded-2xl bg-white border border-ink-200 shadow-card">
                    <label class="flex items-center gap-2 text-xs text-ink-500 font-semibold">
                        <span>Desde:</span>
                        <input type="date" name="desde" required
                               value="{{ request('desde', now()->startOfMonth()->toDateString()) }}"
                               class="rounded-xl bg-ink-50 border border-ink-200 text-ink-900 text-xs py-1.5 px-2.5 focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600" />
                    </label>
                    <label class="flex items-center gap-2 text-xs text-ink-500 font-semibold">
                        <span>Hasta:</span>
                        <input type="date" name="hasta" required
                               value="{{ request('hasta', now()->toDateString()) }}"
                               class="rounded-xl bg-ink-50 border border-ink-200 text-ink-900 text-xs py-1.5 px-2.5 focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600" />
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button type="submit" formaction="{{ route('reportes.salidas.pdf') }}"
                            class="group p-6 rounded-2xl bg-white border border-ink-200 hover:border-brand-600 shadow-card hover:shadow-elevated transition-all flex items-center gap-4 text-left">
                        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-base font-semibold text-ink-900 group-hover:text-brand-600 transition-colors">PDF</span>
                            <span class="block text-xs text-ink-500">Ver el acta en el visor de PDF</span>
                        </div>
                    </button>
                    <button type="submit" formaction="{{ route('reportes.salidas.excel') }}"
                            class="group p-6 rounded-2xl bg-white border border-ink-200 hover:border-brand-600 shadow-card hover:shadow-elevated transition-all flex items-center gap-4 text-left">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-base font-semibold text-ink-900 group-hover:text-brand-600 transition-colors">Excel</span>
                            <span class="block text-xs text-ink-500">Ver el acta como hoja de cálculo</span>
                        </div>
                    </button>
                </div>
            </form>
        @elseif ($reportes === null)
            <!-- Sin reporte seleccionado -->
            <div class="bg-white border border-ink-200 rounded-2xl shadow-card p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-ink-50 border border-ink-200 flex items-center justify-center">
                    <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink-900">Selecciona un reporte</h3>
            </div>
        @else
        <!-- Table List -->
        <div class="bg-white border border-ink-200 rounded-2xl shadow-card overflow-hidden">
            @if ($reportes->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-ink-50 text-ink-500 border border-ink-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-ink-900">No se han emitido reportes</h3>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-ink-200 bg-ink-50 text-[11px] font-bold uppercase tracking-wider text-ink-500">
                                <th class="py-4 px-6">Código</th>
                                <th class="py-4 px-6">Tipo de Reporte</th>
                                <th class="py-4 px-6">Fecha de Emisión</th>
                                <th class="py-4 px-6">Generado Por</th>
                                <th class="py-4 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 text-sm">
                            @foreach ($reportes as $reporte)
                                <tr class="hover:bg-ink-50/80 transition-colors">
                                    <td class="py-4 px-6 font-mono text-brand-600 font-bold text-xs">
                                        #REP-{{ str_pad($reporte->id_reporte, 4, '0', STR_PAD_LEFT) }}
                                    </td>

                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-ink-50 text-brand-600 border border-ink-200 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <span class="font-bold text-ink-900">{{ $reporte->tipo }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6 text-ink-500 text-xs font-mono">
                                        {{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y') }}
                                    </td>

                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-ink-900 text-white flex items-center justify-center text-xs font-bold">
                                                {{ strtoupper(substr($reporte->usuario?->nombre ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-ink-900">{{ $reporte->usuario?->nombre ?? 'Sistema' }}</p>
                                                <span class="text-[11px] text-ink-500 font-mono">{{ $reporte->usuario?->rol?->nombre ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('reportes.show', $reporte) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-ink-50 hover:bg-ink-100 border border-ink-200 text-ink-900 text-xs font-semibold transition-colors"
                                               title="Ver e imprimir reporte">
                                                <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Ver Documento</span>
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('reportes.destroy', $reporte) }}"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar el reporte #REP-{{ str_pad($reporte->id_reporte, 4, '0', STR_PAD_LEFT) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 rounded-xl bg-ink-50 hover:bg-rose-50 border border-ink-200 hover:border-rose-200 text-ink-500 hover:text-rose-600 transition-all"
                                                        title="Eliminar reporte">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($reportes->hasPages())
                    <div class="p-4 border-t border-ink-200 bg-ink-50">
                        {{ $reportes->links() }}
                    </div>
                @endif
            @endif
        </div>
        @endif
    </div>
</x-app-layout>
