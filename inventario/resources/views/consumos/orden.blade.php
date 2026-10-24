@php
    $codigoOrden = 'ORD-' . str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT);
    $bodegas = $orden->consumos->map(fn ($c) => $c->producto?->almacen?->nombre)->filter()->unique();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('consumos.index', ['fecha' => \Carbon\Carbon::parse($orden->fecha)->toDateString()]) }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Comprobante #{{ $codigoOrden }}</h1>
        </div>
    </x-slot>

    <!-- Estilos específicos para impresión (Ticket Térmico 80mm) -->
    <style>
        @media print {
            body {
                background-color: #ffffff !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .ticket-print-area {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 2mm !important;
                box-shadow: none !important;
                border: none !important;
                font-size: 11px !important;
            }
            @page {
                margin: 6mm;
            }
        }
    </style>

    <div class="max-w-4xl mx-auto space-y-6"
         x-data
         x-init="$nextTick(() => {
             if (!window.JsBarcode) return;
             try {
                 window.JsBarcode('#barcode-orden', '{{ $codigoOrden }}', {
                     format: 'CODE128', lineColor: '#000000', textColor: '#000000', width: 1.3, height: 28, displayValue: true, fontSize: 10, margin: 2
                 });
             } catch (e) {
                 console.warn('Error al renderizar código de barras:', e);
             }
         })">

        <!-- Botones de Acción (Ocultos al Imprimir) -->
        <div class="max-w-[360px] mx-auto print:hidden">
            <button type="button"
                    onclick="window.print()"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Imprimir Comprobante</span>
            </button>
        </div>

        <!-- Comprobante en formato ticket térmico (80mm) -->
        <div class="ticket-print-area max-w-[360px] mx-auto bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm font-mono text-xs text-[#1E293B]">

            <!-- Encabezado Ticket -->
            <div class="text-center pb-4 border-b border-dashed border-[#1E293B]">
                <h3 class="text-lg font-black tracking-tight text-[#1E293B] uppercase">INVENTORY</h3>
                <p class="text-[11px] font-bold text-[#64748B] tracking-wide mt-1">DESPACHO N.º {{ str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT) }}</p>
            </div>

            <!-- Metadatos de la Entrega -->
            <div class="py-3 border-b border-dashed border-[#1E293B] space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-[#64748B]">FECHA:</span>
                    <span class="font-bold">{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }} {{ $orden->created_at?->format('H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#64748B]">BODEGA:</span>
                    <span class="font-bold text-right truncate max-w-[190px]">{{ $bodegas->implode(', ') ?: 'Central' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#64748B]">TÉCNICO:</span>
                    <span class="font-bold text-right truncate max-w-[190px]">{{ $orden->usuario?->nombre }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#64748B]">CÉDULA:</span>
                    <span class="font-bold font-mono">{{ $orden->numero_cedula }}</span>
                </div>
            </div>

            <!-- Detalle de los Equipos Retirados -->
            <div class="py-3 border-b border-dashed border-[#1E293B]">
                <div class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] mb-2">
                    <span>CANT / ARTÍCULO</span>
                </div>
                <div class="divide-y divide-dotted divide-[#CBD5E1]">
                    @foreach ($orden->consumos as $c)
                        <div class="py-2 first:pt-0 last:pb-0">
                            <div class="font-bold text-[12px]">{{ $c->cantidad ?? 1 }}x {{ $c->producto?->nombre ?? 'Equipo' }}</div>
                            <div class="text-[10px] text-[#64748B] mt-0.5">S/N: <span class="font-bold text-[#1E293B]">{{ $c->numero_serie }}</span></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 pt-2 border-t border-dotted border-[#CBD5E1] flex justify-between font-bold text-[11px]">
                    <span>TOTAL</span>
                    <span>{{ $orden->consumos->count() }} {{ $orden->consumos->count() === 1 ? 'equipo' : 'equipos' }} &bull; {{ $orden->consumos->sum('cantidad') }} unid.</span>
                </div>
            </div>

            <!-- Motivo del Retiro / OT -->
            <div class="py-3 border-b border-dashed border-[#1E293B] text-[10px]">
                <span class="font-bold text-[#64748B] block uppercase text-[9px]">MOTIVO / ORDEN DE TRABAJO:</span>
                <p class="text-[#1E293B] italic mt-0.5 break-words">
                    &ldquo;{{ $orden->motivo }}&rdquo;
                </p>
            </div>

            <!-- Firma de Conformidad Ticket -->
            <div class="pt-5 pb-2 text-center text-[10px]">
                <p class="text-[9px] text-[#64748B] mb-20 leading-tight">
                    Certifico la recepción de los equipos y su custodia asignada para labores técnicas.
                </p>

                <div class="border-t border-[#1E293B] w-56 mx-auto pt-1 mb-1">
                    <p class="font-bold text-[#1E293B] text-[11px]">{{ $orden->usuario?->nombre }}</p>
                    <p class="text-[9px] text-[#64748B]">FIRMA TÉCNICO RECEPTOR</p>
                    <p class="text-[9px] font-mono text-[#94A3B8]">CI: {{ $orden->numero_cedula }}</p>
                </div>

                <!-- Código de barras de la orden -->
                <div class="mt-5 pt-3 border-t border-dotted border-[#E2E8F0] flex justify-center">
                    <svg id="barcode-orden" class="max-h-10"></svg>
                </div>

                <div class="mt-2 text-[9px] text-[#94A3B8] uppercase tracking-wider">
                    *** SISTEMA INVENTORY &bull; VALE VÁLIDO ***
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
