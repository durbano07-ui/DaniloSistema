<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('consumos.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Comprobante #CNS-{{ str_pad($consumo->id_consumo, 4, '0', STR_PAD_LEFT) }}</h1>
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
         x-data="{
             renderBarcodes() {
                 this.$nextTick(() => {
                     if (!window.JsBarcode) return;
                     try {
                         // Código para Formato Ticket Térmico 80mm
                         if (document.querySelector('#barcode-vale-ticket')) {
                             window.JsBarcode('#barcode-vale-ticket', 'CNS-{{ str_pad($consumo->id_consumo, 4, '0', STR_PAD_LEFT) }}', {
                                 format: 'CODE128',
                                 lineColor: '#2563EB',
                                 width: 1.3,
                                 height: 28,
                                 displayValue: true,
                                 fontSize: 10,
                                 margin: 2
                             });
                         }
                         if (document.querySelector('#barcode-serie-ticket')) {
                             window.JsBarcode('#barcode-serie-ticket', '{{ $consumo->numero_serie }}', {
                                 format: 'CODE128',
                                 lineColor: '#2563EB',
                                 width: 1.3,
                                 height: 28,
                                 displayValue: true,
                                 fontSize: 10,
                                 margin: 2
                             });
                         }
                     } catch(e) {
                         console.warn('Error al renderizar código de barras:', e);
                     }
                 });
             }
         }"
         x-init="renderBarcodes()">

        <!-- Notificación de Éxito al Generar Salida -->
        @if (session('status'))
            <div class="p-4 rounded-2xl bg-[#F0FDF4] border border-[#BBF7D0] text-[#15803D] flex items-center justify-between gap-4 shadow-sm print:hidden">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#DCFCE7] flex items-center justify-center flex-shrink-0 text-[#16A34A]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-[#14532D]">Comprobante Generado y Almacenado</p>
                        <p class="text-xs text-[#15803D] mt-0.5">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Bar (Oculto al Imprimir) -->
        <div class="max-w-[360px] mx-auto flex items-center justify-between gap-3 print:hidden">
            <!-- Botones de Acción -->
            <div class="w-full flex items-center justify-between gap-3">
                <!-- Botón Imprimir Comprobante -->
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Imprimir Comprobante</span>
                </button>

                <!-- Botón Anular Retiro -->
                @can('registrar-consumo')
                    <form method="POST"
                          action="{{ route('consumos.destroy', $consumo) }}"
                          onsubmit="return confirm('¿Deseas revertir este retiro técnico?\n\nEl equipo volverá a estar DISPONIBLE y su stock se restaurará.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-[#F8FAFC] hover:bg-[#EFF6FF] text-[#64748B] hover:text-[#2563EB] border border-[#BFDBFE] hover:border-[#BFDBFE] text-xs font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Anular</span>
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Comprobante en formato ticket térmico (80mm) -->
        <div class="ticket-print-area max-w-[360px] mx-auto bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm font-mono text-xs text-[#1E293B]">
            
            <!-- Encabezado Ticket -->
            <div class="text-center pb-4 border-b border-dashed border-[#1E293B]">
                <h3 class="text-lg font-black tracking-tight text-[#1E293B] uppercase">INVENTORY</h3>
                <p class="text-[10px] font-bold text-[#64748B] tracking-wide mt-0.5">CONTROL DE DESPACHOS Y MATERIALES</p>
                <p class="text-[9px] text-[#94A3B8]">RUC / ID: 0201849201001 &bull; Ecuador</p>
                
                <div class="mt-3 pt-2 border-t border-dotted border-[#E2E8F0]">
                    <span class="inline-block px-2.5 py-0.5 bg-[#1E293B] text-white text-[11px] font-bold rounded">
                        VALE #CNS-{{ str_pad($consumo->id_consumo, 4, '0', STR_PAD_LEFT) }}
                    </span>
                    <div class="flex justify-center my-1.5">
                        <svg id="barcode-vale-ticket" class="max-h-10"></svg>
                    </div>
                </div>
            </div>

            <!-- Metadatos de la Entrega -->
            <div class="py-3 border-b border-dashed border-[#1E293B] space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-[#64748B]">FECHA:</span>
                    <span class="font-bold">{{ \Carbon\Carbon::parse($consumo->fecha)->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#64748B]">BODEGA:</span>
                    <span class="font-bold text-right truncate max-w-[190px]">{{ $consumo->producto?->almacen?->nombre ?? 'Central' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#64748B]">TÉCNICO:</span>
                    <span class="font-bold text-right truncate max-w-[190px]">{{ $consumo->usuario?->nombre }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#64748B]">CÉDULA:</span>
                    <span class="font-bold font-mono">{{ $consumo->numero_cedula }}</span>
                </div>
            </div>

            <!-- Detalle del Equipo Retirado -->
            <div class="py-3 border-b border-dashed border-[#1E293B]">
                <div class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] mb-1.5 flex justify-between">
                    <span>CANT / ARTÍCULO</span>
                    <span>ESTADO</span>
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between font-bold text-[12px]">
                        <span>{{ $consumo->cantidad ?? 1 }}x {{ $consumo->producto?->nombre ?? 'Equipo' }}</span>
                        <span class="text-[#2563EB] text-[10px] font-bold">[SALIDA]</span>
                    </div>
                    <div class="text-[10px] text-[#64748B]">
                        <span>CAT: {{ $consumo->producto?->categoria?->nombre ?? 'General' }}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-[10px] font-bold text-[#1E293B] block">SERIE / CÓDIGO:</span>
                        <span class="text-[11px] font-bold text-[#2563EB] block font-mono">{{ $consumo->numero_serie }}</span>
                        <div class="flex justify-center my-1">
                            <svg id="barcode-serie-ticket" class="max-h-9"></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivo del Retiro / OT -->
            <div class="py-3 border-b border-dashed border-[#1E293B] text-[10px]">
                <span class="font-bold text-[#64748B] block uppercase text-[9px]">MOTIVO / ORDEN DE TRABAJO:</span>
                <p class="text-[#1E293B] italic mt-0.5 break-words">
                    &ldquo;{{ $consumo->motivo }}&rdquo;
                </p>
            </div>

            <!-- Firma de Conformidad Ticket -->
            <div class="pt-5 pb-2 text-center text-[10px]">
                <p class="text-[9px] text-[#64748B] mb-8 leading-tight">
                    Certifico la recepción del equipo y su custodia asignada para labores técnicas.
                </p>

                <div class="border-t border-[#1E293B] w-48 mx-auto pt-1 mb-1">
                    <p class="font-bold text-[#1E293B] text-[11px]">{{ $consumo->usuario?->nombre }}</p>
                    <p class="text-[9px] text-[#64748B]">FIRMA TÉCNICO RECEPTOR</p>
                    <p class="text-[9px] font-mono text-[#94A3B8]">CI: {{ $consumo->numero_cedula }}</p>
                </div>

                <div class="mt-5 pt-3 border-t border-dotted border-[#E2E8F0] text-[9px] text-[#94A3B8] uppercase tracking-wider">
                    *** SISTEMA INVENTORY &bull; VALE VÁLIDO ***
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
