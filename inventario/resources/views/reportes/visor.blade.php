<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ $volver }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">{{ $titulo }}</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-600 border border-brand-200">
                {{ $formato === 'pdf' ? 'PDF' : 'Excel' }}
            </span>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-end">
            <a href="{{ $descarga }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ $formato === 'pdf' ? 'Descargar PDF' : 'Descargar Excel' }}</span>
            </a>
        </div>

        @if ($formato === 'pdf')
            <!-- Visor de PDF del navegador -->
            <div class="rounded-2xl border border-ink-200 bg-white overflow-hidden shadow-card">
                <iframe src="{{ $archivo }}"
                        title="{{ $titulo }} en PDF"
                        class="w-full h-[calc(100vh-14rem)] min-h-[480px]"></iframe>
            </div>
        @else
            <!-- Visor tipo hoja de cálculo -->
            <div class="rounded-2xl border border-ink-200 bg-white overflow-hidden shadow-card">
                <div class="flex items-center gap-2 px-4 py-2 border-b border-ink-200 bg-ink-50 text-xs text-ink-500">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                    <span class="font-semibold text-ink-900">{{ $nombreArchivo }}</span>
                </div>
                <div class="overflow-auto max-h-[calc(100vh-16rem)]">
                    <table class="border-collapse text-xs font-[Calibri,Arial,sans-serif] min-w-full">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-ink-100 text-ink-500">
                                <th class="w-10 border border-ink-300 bg-ink-200"></th>
                                @foreach (array_slice(range('A', 'Z'), 0, count($columnas)) as $letra)
                                    <th class="border border-ink-300 px-3 py-1 font-normal text-center">{{ $letra }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-ink-300 bg-ink-100 text-center text-ink-500">1</td>
                                @foreach ($columnas as $columna)
                                    <td class="border border-ink-300 px-3 py-1.5 bg-ink-900 text-white font-bold whitespace-nowrap">{{ $columna }}</td>
                                @endforeach
                            </tr>
                            @forelse ($filas as $i => $fila)
                                <tr class="hover:bg-brand-50">
                                    <td class="border border-ink-300 bg-ink-100 text-center text-ink-500">{{ $i + 2 }}</td>
                                    @foreach ($fila as $j => $valor)
                                        <td class="border border-ink-200 px-3 py-1.5 text-ink-900 align-top {{ is_string($valor) && str_contains($valor, "\n") ? 'whitespace-pre-line min-w-64' : 'whitespace-nowrap' }} {{ is_int($valor) ? 'text-right font-mono' : '' }}">{{ $valor }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td class="border border-ink-300 bg-ink-100 text-center text-ink-500">2</td>
                                    <td colspan="{{ count($columnas) }}" class="border border-ink-200 px-3 py-1.5 text-ink-400">Sin registros</td>
                                </tr>
                            @endforelse
                            @if ($columnaTotal !== null)
                                <tr class="font-bold">
                                    <td class="border border-ink-300 bg-ink-100 text-center text-ink-500 font-normal">{{ $filas->count() + 2 }}</td>
                                    <td colspan="{{ $columnaTotal }}" class="border border-ink-200 px-3 py-1.5 text-ink-900">Total en stock</td>
                                    <td class="border border-ink-200 px-3 py-1.5 text-right font-mono text-ink-900">{{ $filas->sum($columnaTotal) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
