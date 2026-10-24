<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('consumos.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Salida de Equipos &bull; Lector de Código de Barras</h1>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6"
         x-data="{
             scanCode: '',
             isScanningCamera: false,
             html5QrCode: null,
             isLoading: false,
             scanError: null,
             scanSuccess: null,
             manualMode: false,
             selectedManualSerie: '',
             scanTimer: null,

             // TABLA ACUMULATIVA DE EQUIPOS PARA SALIDA / PEDIDO
             items: [],

             init() {
                 this.$nextTick(() => {
                     this.focusScanner();
                     @if($initialSerie)
                         this.lookupAndAdd('{{ $initialSerie }}');
                     @endif
                 });

                 // Captura global de teclado: Si el operador dispara el lector aunque no tenga el clic en el input
                 window.addEventListener('keydown', (e) => {
                     if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                         if (e.key.length === 1 || e.key === 'Enter') {
                             this.focusScanner();
                         }
                     }
                 });
             },
             focusScanner() {
                 if (this.$refs.barcodeInput) {
                     this.$refs.barcodeInput.focus();
                 }
             },
             onScanInput() {
                 clearTimeout(this.scanTimer);
                 const code = this.scanCode.trim();
                 // Si el escáner no envía Enter, auto-disparar consulta 350ms tras recibir los caracteres
                 if (code.length >= 3) {
                     this.scanTimer = setTimeout(() => {
                         this.handleScanSubmit();
                     }, 350);
                 }
             },
             async handleScanSubmit() {
                 clearTimeout(this.scanTimer);
                 const code = this.scanCode.trim();
                 if (!code) return;
                 console.log('[Lector Barcode] Código detectado:', code);
                 await this.lookupAndAdd(code);
                 this.scanCode = '';
                 this.focusScanner();
             },
             async lookupAndAdd(serie) {
                 this.isLoading = true;
                 this.scanError = null;
                 this.scanSuccess = null;
                 console.log('[Inventario] Consultando serie en servidor:', serie);

                 try {
                     const response = await fetch(`/productos/scan/${encodeURIComponent(serie)}`, {
                         headers: {
                             'Accept': 'application/json',
                             'X-Requested-With': 'XMLHttpRequest'
                         }
                     });

                     const data = await response.json();
                     console.log('[Inventario] Respuesta recibida:', data);

                     if (!response.ok || !data.encontrado) {
                         window.playScannerBeep('error');
                         this.scanError = data.mensaje || `No se encontró ningún equipo con el código '${serie}'.`;
                         this.focusScanner();
                         return;
                     }

                     if (!data.es_disponible) {
                         window.playScannerBeep('error');
                         let msg = `El equipo '${data.producto.nombre}' (Serie: ${data.producto.numero_serie}) NO está disponible (Estado: ${data.producto.estado.toUpperCase()}).`;
                         if (data.ultimo_consumo) {
                             msg += ` Fue retirado el ${data.ultimo_consumo.fecha} por ${data.ultimo_consumo.tecnico}.`;
                         }
                         this.scanError = msg;
                         this.focusScanner();
                         return;
                     }

                     // Agregar producto a la tabla acumulativa
                     this.addItem(data.producto);
                 } catch (err) {
                     window.playScannerBeep('error');
                     this.scanError = 'Ocurrió un error al consultar el catálogo. Verifica tu conexión.';
                     this.focusScanner();
                 } finally {
                     this.isLoading = false;
                 }
             },
             addItem(prod) {
                 const existingIndex = this.items.findIndex(i => i.numero_serie === prod.numero_serie);
                 if (existingIndex !== -1) {
                     const item = this.items[existingIndex];
                     if (item.tipo === 'lote') {
                         if (item.cantidad < item.cantidad_disponible) {
                             item.cantidad++;
                             window.playScannerBeep('success');
                             this.scanSuccess = `Se incrementó la cantidad del lote ${item.nombre}: ${item.cantidad} ${this.etiquetaUnidad(item)}.`;
                         } else {
                             window.playScannerBeep('error');
                             this.scanError = `El lote '${item.nombre}' ya tiene asignado todo el stock disponible (${item.cantidad_disponible} ${this.etiquetaUnidad(item)}).`;
                         }
                     } else {
                         window.playScannerBeep('error');
                         this.scanError = `El equipo individual '${item.nombre}' (${item.numero_serie}) ya está agregado a esta salida.`;
                     }
                     this.focusScanner();
                     return;
                 }

                 // Añadir nuevo ítem al pedido
                 this.items.push({
                     numero_serie: prod.numero_serie,
                     nombre: prod.nombre,
                     categoria: prod.categoria,
                     marca: prod.marca || null,
                     modelo: prod.modelo || null,
                     almacen: prod.almacen,
                     tipo: prod.tipo || 'unitario',
                     cantidad_disponible: parseInt(prod.cantidad_disponible) || 1,
                     unidad: (prod.tipo === 'lote' && prod.unidad === 'metros') ? 'metros' : 'unidades',
                     cantidad: 1
                 });

                 window.playScannerBeep('success');
                 this.scanSuccess = `Agregado al pedido: ${prod.nombre} (${prod.numero_serie})`;
                 this.focusScanner();
             },
             removeItem(index) {
                 this.items.splice(index, 1);
                 this.focusScanner();
             },
             clearAll() {
                 if (confirm('¿Deseas vaciar todos los equipos agregados a esta salida?')) {
                     this.items = [];
                     this.focusScanner();
                 }
             },
             etiquetaUnidad(item) {
                 return item.unidad === 'metros' ? 'm' : 'unidades';
             },
             get totalUnidades() {
                 return this.items.filter(i => i.unidad !== 'metros').reduce((acc, item) => acc + (parseInt(item.cantidad) || 1), 0);
             },
             get totalMetros() {
                 return this.items.filter(i => i.unidad === 'metros').reduce((acc, item) => acc + (parseInt(item.cantidad) || 0), 0);
             },
             get resumenCantidades() {
                 const partes = [];
                 if (this.totalUnidades > 0) partes.push(this.totalUnidades + (this.totalUnidades === 1 ? ' unidad' : ' unidades'));
                 if (this.totalMetros > 0) partes.push(this.totalMetros + ' m');
                 return partes.join(' + ');
             },
             toggleCamera() {
                 if (this.isScanningCamera) {
                     this.stopCamera();
                 } else {
                     this.startCamera();
                 }
             },
             async startCamera() {
                 this.isScanningCamera = true;
                 this.$nextTick(async () => {
                     try {
                         this.html5QrCode = new window.Html5Qrcode('camera-reader');
                         await this.html5QrCode.start(
                             { facingMode: 'environment' },
                             {
                                 fps: 15,
                                 qrbox: { width: 280, height: 160 }
                             },
                             (decodedText) => {
                                 this.stopCamera();
                                 this.lookupAndAdd(decodedText);
                             },
                             () => {}
                         );
                     } catch (err) {
                         alert('No se pudo acceder a la cámara. Asegúrate de otorgar permisos en tu navegador.');
                         this.stopCamera();
                     }
                 });
             },
             async stopCamera() {
                 if (this.html5QrCode) {
                     try {
                         await this.html5QrCode.stop();
                         this.html5QrCode.clear();
                     } catch (e) {}
                     this.html5QrCode = null;
                 }
                 this.isScanningCamera = false;
                 this.focusScanner();
             }
         }">

        <!-- Banner Modo Escáner Físico / Pistola -->
        <div class="p-6 rounded-2xl bg-white border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04)] relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-4v4m0-4v-4m0 4h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-base font-bold text-[#1E293B] tracking-tight">Terminal de Salida por Código de Barras</h2>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE]">
                                <span class="w-2 h-2 rounded-full bg-[#2563EB] animate-ping"></span>
                                Pistola Lectora Activa
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="toggleCamera()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-[#F8FAFC] hover:bg-[#F1F5F9] text-[#1E293B] text-xs font-semibold border border-[#E2E8F0] transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span x-text="isScanningCamera ? 'Cerrar Cámara' : 'Escanear con Cámara'"></span>
                    </button>

                    <button type="button"
                            @click="manualMode = !manualMode"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-[#F8FAFC] hover:bg-[#F1F5F9] text-[#64748B] hover:text-[#1E293B] text-xs font-semibold border border-[#E2E8F0] transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span x-text="manualMode ? 'Ocultar Selector' : 'Buscar del Catálogo'"></span>
                    </button>
                </div>
            </div>

            <!-- Visor de Cámara Web/Móvil (cuando se activa) -->
            <div x-show="isScanningCamera"
                 x-transition
                 class="mt-4 p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-center"
                 style="display: none;">
                <p class="text-xs text-[#2563EB] font-semibold mb-3">Enfoca el código de barras o QR con la cámara:</p>
                <div id="camera-reader" class="w-full max-w-sm mx-auto overflow-hidden rounded-xl bg-black"></div>
                <button type="button"
                        @click="stopCamera()"
                        class="mt-3 px-4 py-1.5 rounded-full bg-white border border-[#E2E8F0] text-[#64748B] text-xs hover:text-[#1E293B] cursor-pointer">
                    Cancelar Escaneo
                </button>
            </div>

            <!-- Campo Principal del Lector de Código de Barras -->
            <div class="mt-6">
                <label for="barcode-scanner-input" class="block text-xs font-bold text-[#2563EB] uppercase tracking-wider mb-2">
                    Disparo de Pistola / Ingreso Rápido de Serie:
                </label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#2563EB]">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-4v4m0-4v-4m0 4h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <input type="text"
                           id="barcode-scanner-input"
                           x-ref="barcodeInput"
                           x-model="scanCode"
                           @input="onScanInput()"
                           @keydown.enter.prevent="handleScanSubmit()"
                           @keydown.tab.prevent="handleScanSubmit()"
                           placeholder="Escanea el código con el lector o ingresa la serie..."
                           autocomplete="off"
                           autofocus
                           class="w-full pl-12 pr-28 py-3.5 bg-[#F8FAFC] border-2 border-[#E2E8F0] focus:border-[#2563EB] focus:ring-4 focus:ring-[#2563EB]/10 rounded-xl text-[#1E293B] font-mono text-base placeholder-[#94A3B8] transition-all tracking-wider" />

                    <div class="absolute inset-y-0 right-1.5 flex items-center gap-1.5 pr-2">
                        <button type="button"
                                @click="handleScanSubmit()"
                                :disabled="isLoading || !scanCode.trim()"
                                class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] disabled:opacity-40 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                            <span x-show="!isLoading">+ Añadir</span>
                            <span x-show="isLoading" class="inline-flex items-center">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Mensajes Reactivos de Retroalimentación del Escaneo -->
                <div x-show="scanError" x-transition class="mt-3 p-3.5 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-rose-700 text-xs flex items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium" x-text="scanError"></span>
                    </div>
                    <button type="button" @click="scanError = null; focusScanner();" class="text-rose-600 hover:text-rose-900 cursor-pointer">&times;</button>
                </div>

                <div x-show="scanSuccess" x-transition class="mt-3 p-3 rounded-xl bg-[#F0FDF4] border border-[#BBF7D0] text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-semibold" x-text="scanSuccess"></span>
                    </div>
                    <button type="button" @click="scanSuccess = null" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">&times;</button>
                </div>
            </div>
        </div>

        <!-- Selector Manual Desplegable (Modo Alternativo) -->
        <div x-show="manualMode" x-transition class="p-6 rounded-2xl bg-white border border-[#E2E8F0] space-y-3 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Añadir Manualmente desde el Catálogo</span>
                <span class="text-xs text-[#94A3B8] font-medium">{{ $productosDisponibles->count() }} equipos disponibles</span>
            </div>
            <select x-model="selectedManualSerie"
                    @change="if (selectedManualSerie) { lookupAndAdd(selectedManualSerie); selectedManualSerie = ''; }"
                    class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] text-sm focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 cursor-pointer">
                <option value="">-- Elige un equipo para añadirlo a la tabla del pedido --</option>
                @foreach ($productosDisponibles as $prod)
                    <option value="{{ $prod->numero_serie }}">
                        [{{ $prod->numero_serie }}] {{ $prod->nombre }} &bull; {{ $prod->marca?->nombre ?? '' }} {{ $prod->modelo?->nombre ?? '' }} &bull; {{ ($prod->tipo ?? 'unitario') === 'lote' ? 'Lote (' . $prod->cantidad_disponible . ($prod->esMetros() ? ' m' : '') . ' disp.)' : '1 unidad' }} &bull; Bodega: {{ $prod->almacen?->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- FORMULARIO DE SALIDA CON TABLA ACUMULATIVA -->
        <form method="POST" action="{{ route('consumos.store') }}" class="space-y-6">
            @csrf

            <!-- Campos Ocultos para cada uno de los items del pedido -->
            <template x-for="(item, index) in items" :key="item.numero_serie">
                <div>
                    <input type="hidden" :name="'items[' + index + '][numero_serie]'" :value="item.numero_serie" />
                    <input type="hidden" :name="'items[' + index + '][cantidad]'" :value="item.cantidad" />
                </div>
            </template>

            <!-- Compatibilidad si solo se agrega un item -->
            <input type="hidden" name="numero_serie" :value="items.length > 0 ? items[0].numero_serie : ''" />
            <input type="hidden" name="cantidad" :value="items.length > 0 ? items[0].cantidad : 1" />

            <!-- ==================== 1. TABLA ACUMULATIVA DEL PEDIDO ==================== -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-7 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#F1F5F9]">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#EFF6FF] text-[#2563EB] font-semibold text-sm flex items-center justify-center border border-[#BFDBFE]">
                                1
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-[#1E293B]">Equipos en la Orden de Salida</h2>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0]"
                              x-show="items.length > 0">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span x-text="items.length + (items.length === 1 ? ' producto' : ' productos')"></span>
                            <span>&bull;</span>
                            <span x-text="resumenCantidades"></span>
                        </span>

                        <button type="button"
                                x-show="items.length > 0"
                                @click="clearAll()"
                                class="px-3 py-1 rounded-xl text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors cursor-pointer">
                            Vaciar lista
                        </button>
                    </div>
                </div>

                <!-- Estado Vacío -->
                <template x-if="items.length === 0">
                    <div class="p-8 rounded-xl bg-[#F8FAFC] border-2 border-dashed border-[#E2E8F0] text-center space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-white text-[#2563EB] border border-[#E2E8F0] flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-4v4m0-4v-4m0 4h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-[#1E293B]">El pedido está vacío</h4>
                    </div>
                </template>

                <!-- Tabla con los productos acumulados -->
                <template x-if="items.length > 0">
                    <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#F8FAFC] text-[#64748B] uppercase font-bold text-[10px] tracking-wider border-b border-[#E2E8F0]">
                                    <th class="py-3 px-3 w-8 text-center">#</th>
                                    <th class="py-3 px-4">Serie / Código</th>
                                    <th class="py-3 px-4">Equipo / Marca / Modelo</th>
                                    <th class="py-3 px-4">Bodega Origen</th>
                                    <th class="py-3 px-4 text-center">Cantidad a Retirar</th>
                                    <th class="py-3 px-3 text-right">Quitar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F5F9] bg-white">
                                <template x-for="(item, index) in items" :key="item.numero_serie">
                                    <tr class="hover:bg-[#F8FAFC]/60 transition-colors">
                                        <!-- Index -->
                                        <td class="py-3.5 px-3 text-center font-mono font-bold text-[#94A3B8]" x-text="index + 1"></td>

                                        <!-- Serie / Código -->
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="font-mono font-bold text-[#2563EB] text-xs" x-text="item.numero_serie"></span>
                                            <div class="mt-0.5">
                                                <template x-if="item.tipo === 'lote'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE]">
                                                        Lote (<span x-text="item.cantidad_disponible + (item.unidad === 'metros' ? ' m' : '')"></span> disp.)
                                                    </span>
                                                </template>
                                                <template x-if="item.tipo !== 'lote'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#F8FAFC] text-[#64748B] border border-[#E2E8F0]">
                                                        Individual
                                                    </span>
                                                </template>
                                            </div>
                                        </td>

                                        <!-- Producto / Clasificación -->
                                        <td class="py-3.5 px-4">
                                            <p class="font-bold text-[#1E293B] text-xs" x-text="item.nombre"></p>
                                            <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-[#64748B]">
                                                <span class="font-medium" x-text="item.categoria"></span>
                                                <template x-if="item.marca">
                                                    <span class="text-[#94A3B8]">&bull;</span>
                                                </template>
                                                <template x-if="item.marca">
                                                    <span class="font-semibold text-[#1E293B]" x-text="item.marca + (item.modelo ? ' ' + item.modelo : '')"></span>
                                                </template>
                                            </div>
                                        </td>

                                        <!-- Bodega -->
                                        <td class="py-3.5 px-4 whitespace-nowrap text-[#475569]">
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                <span class="font-medium" x-text="item.almacen"></span>
                                            </div>
                                        </td>

                                        <!-- Cantidad a Despachar -->
                                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                            <template x-if="item.tipo === 'lote'">
                                                <div class="inline-flex items-center gap-1.5 bg-[#F8FAFC] p-1 rounded-xl border border-[#E2E8F0]">
                                                    <button type="button"
                                                            @click="if (item.cantidad > 1) item.cantidad--"
                                                            class="w-6 h-6 rounded-lg bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#E2E8F0] flex items-center justify-center font-bold text-xs border border-[#E2E8F0] cursor-pointer">
                                                        -
                                                    </button>
                                                    <input type="number"
                                                           x-model.number="item.cantidad"
                                                           min="1"
                                                           :max="item.cantidad_disponible"
                                                           @change="if (item.cantidad < 1) item.cantidad = 1; if (item.cantidad > item.cantidad_disponible) item.cantidad = item.cantidad_disponible;"
                                                           class="w-14 py-0.5 px-1 text-center font-mono font-bold text-xs bg-white border border-[#E2E8F0] rounded-lg text-[#1E293B]" />
                                                    <button type="button"
                                                            @click="if (item.cantidad < item.cantidad_disponible) item.cantidad++"
                                                            class="w-6 h-6 rounded-lg bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#E2E8F0] flex items-center justify-center font-bold text-xs border border-[#E2E8F0] cursor-pointer">
                                                        +
                                                    </button>
                                                    <span class="text-[10px] text-[#64748B] px-1 font-semibold" x-text="item.unidad === 'metros' ? 'm' : 'unids.'"></span>
                                                </div>
                                            </template>
                                            <template x-if="item.tipo !== 'lote'">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] font-semibold text-xs">
                                                    1 unidad
                                                </span>
                                            </template>
                                        </td>

                                        <!-- Quitar -->
                                        <td class="py-3.5 px-3 text-right">
                                            <button type="button"
                                                    @click="removeItem(index)"
                                                    class="p-1.5 rounded-lg text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                    title="Quitar este equipo de la lista">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-[#F8FAFC] border-t border-[#E2E8F0] font-bold text-xs text-[#1E293B]">
                                <tr>
                                    <td colspan="4" class="py-3 px-4 text-right text-[#64748B] uppercase text-[10px] tracking-wider">
                                        Total a Despachar:
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-[#2563EB] text-sm">
                                        <span x-text="resumenCantidades"></span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </template>

                @error('items')
                    <p class="text-xs text-rose-600 font-medium flex items-center gap-1 mt-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
                @error('numero_serie')
                    <p class="text-xs text-rose-600 font-medium flex items-center gap-1 mt-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- ==================== 2. DATOS DEL RETIRO TÉCNICO ==================== -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-7 space-y-4">
                <div class="flex items-center gap-2.5 pb-4 border-b border-[#F1F5F9]">
                    <div class="w-8 h-8 rounded-xl bg-[#EFF6FF] text-[#2563EB] font-semibold text-sm flex items-center justify-center border border-[#BFDBFE]">
                        2
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-[#1E293B]">Datos del Retiro Técnico</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Fecha de Salida -->
                    <div>
                        <label for="fecha" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                            Fecha de Retiro <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha"
                               name="fecha"
                               value="{{ old('fecha', date('Y-m-d')) }}"
                               required
                               class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] font-mono text-sm focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />
                        @error('fecha')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Técnico Receptor -->
                    <div>
                        @if(Auth::user()->tieneRol('Tecnico'))
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                                Técnico Responsable
                            </label>
                            <div class="px-4 py-2.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-sm text-[#1E293B] flex items-center justify-between">
                                <span class="font-bold text-[#1E293B]">{{ Auth::user()->nombre }}</span>
                                <span class="text-xs text-[#64748B] font-mono">{{ Auth::user()->numero_cedula }}</span>
                            </div>
                        @else
                            <label for="numero_cedula" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                                Técnico Receptor / Asignado a: <span class="text-rose-500">*</span>
                            </label>
                            <select id="numero_cedula"
                                    name="numero_cedula"
                                    required
                                    class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] text-sm focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 cursor-pointer">
                                <option value="" disabled {{ old('numero_cedula') ? '' : 'selected' }}>
                                    -- Selecciona el técnico que recibe el equipo --
                                </option>
                                @foreach($tecnicos as $tec)
                                    <option value="{{ $tec->numero_cedula }}" {{ old('numero_cedula') == $tec->numero_cedula ? 'selected' : '' }}>
                                        {{ $tec->nombre }} (CI: {{ $tec->numero_cedula }})
                                    </option>
                                @endforeach
                            </select>
                            @error('numero_cedula')
                                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>

                <!-- Motivo / Orden de Trabajo / Destino -->
                <div>
                    <label for="motivo" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Motivo / Orden de Trabajo / Cliente Destino <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="motivo"
                              name="motivo"
                              rows="3"
                              maxlength="255"
                              required
                              placeholder="Ej. Instalación de acometida, módem y spliters en cliente residencial - OT #5021, Barrio Central"
                              class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">{{ old('motivo') }}</textarea>
                    @error('motivo')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F1F5F9]">
                    <a href="{{ route('consumos.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            :disabled="items.length === 0"
                            :class="items.length > 0 ? 'bg-[#2563EB] hover:bg-[#1D4ED8] text-white cursor-pointer shadow-sm' : 'bg-[#F8FAFC] text-[#94A3B8] cursor-not-allowed border border-[#E2E8F0]'"
                            class="px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Confirmar Salida y Descontar Stock</span>
                        <span x-show="items.length > 0" class="px-2 py-0.5 rounded-full text-xs bg-white/20 text-white font-mono" x-text="items.length"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
