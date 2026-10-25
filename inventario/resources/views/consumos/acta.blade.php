<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('consumos.index', ['fecha' => \Carbon\Carbon::parse($orden->fecha)->toDateString()]) }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all print:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Acta de Entrega #ORD-{{ str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT) }}</h1>
        </div>
    </x-slot>

    <style>
        @media print {
            body { background-color: #ffffff !important; }
            .print\:hidden { display: none !important; }
            .acta-hoja { box-shadow: none !important; border: none !important; padding: 0 !important; max-width: 100% !important; }
            @page { margin: 12mm; }
        }
    </style>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3 print:hidden">
            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-ink-50 border border-ink-200 hover:border-brand-600 text-ink-900 hover:text-brand-600 text-xs font-bold shadow-xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Imprimir</span>
            </button>
            <a href="{{ route('consumos.acta.pdf', $orden) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Descargar PDF</span>
            </a>
        </div>

        <!-- Hoja del Acta -->
        <div class="acta-hoja bg-white rounded-2xl border border-ink-200 shadow-card p-8 sm:p-12">
            @include('consumos._acta-documento', ['logo' => asset('images/logo-acta.png')])
        </div>
    </div>
</x-app-layout>
