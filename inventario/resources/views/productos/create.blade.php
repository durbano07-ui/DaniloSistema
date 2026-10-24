<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('productos.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Registrar Nuevo Producto</h1>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto"
         x-data="{
             seccion: {{ $errors->hasAny(['id_almacen', 'id_proveedor']) ? 3 : ($errors->hasAny(['id_cat', 'id_marca', 'id_modelo', 'nombre']) ? 2 : 1) }},
             vistaCompleta: false,

             tipo: '{{ old('tipo', 'unitario') }}',
             cantidad: {{ old('cantidad', 1) }},
             unidad: '{{ old('unidad', 'unidades') === 'metros' ? 'metros' : 'unidades' }}',
             serie: '{{ old('numero_serie') }}',
             isChecking: false,
             checkStatus: null,
             checkMessage: null,

             // Catálogo
             categorias: @js($categorias),
             idCat: '{{ old('id_cat') }}',
             idMarca: '{{ old('id_marca') }}',
             idModelo: '{{ old('id_modelo') }}',
             nombre: '{{ old('nombre') }}',
             nombreAuto: false,

             // Modales rápidos
             quickMarcaOpen: false,
             quickMarcaNombre: '',
             quickMarcaError: '',
             quickMarcaLoading: false,

             quickModeloOpen: false,
             quickModeloNombre: '',
             quickModeloError: '',
             quickModeloLoading: false,

             init() {
                 this.$nextTick(() => {
                     if (this.$refs.serieInput) this.$refs.serieInput.focus();
                     if (this.serie) this.renderBarcode();
                 });
             },

             get marcasDisponibles() {
                 if (!this.idCat) return [];
                 const cat = this.categorias.find(c => String(c.id_cat) === String(this.idCat));
                 return cat ? (cat.marcas || []) : [];
             },

             get modelosDisponibles() {
                 if (!this.idMarca) return [];
                 for (const c of this.categorias) {
                     const m = (c.marcas || []).find(marca => String(marca.id_marca) === String(this.idMarca));
                     if (m && m.modelos) return m.modelos;
                 }
                 return [];
             },

             onCatChange() {
                 this.idMarca = '';
                 this.idModelo = '';
                 this.sugerirNombre();
             },

             onMarcaChange() {
                 this.idModelo = '';
                 this.sugerirNombre();
             },

             onModeloChange() {
                 this.sugerirNombre();
             },

             sugerirNombre() {
                 const cat = this.categorias.find(c => String(c.id_cat) === String(this.idCat));
                 const marca = this.marcasDisponibles.find(m => String(m.id_marca) === String(this.idMarca));
                 const modelo = this.modelosDisponibles.find(mod => String(mod.id_modelo) === String(this.idModelo));

                 if (cat && marca && modelo) {
                     if (!this.nombre.trim() || this.nombreAuto) {
                         this.nombre = `${cat.nombre} ${marca.nombre} ${modelo.nombre}`;
                         this.nombreAuto = true;
                     }
                 }
             },

             avanzarASeccion2() {
                 if (!this.serie.trim()) {
                     if (this.$refs.serieInput) this.$refs.serieInput.focus();
                     return;
                 }
                 this.seccion = 2;
                 this.$nextTick(() => {
                     if (this.$refs.nombreInput) this.$refs.nombreInput.focus();
                 });
             },

             avanzarASeccion3() {
                 if (!this.idCat) {
                     alert('Por favor selecciona una categoría antes de continuar.');
                     return;
                 }
                 this.seccion = 3;
             },

             // Guardar Marca Rápida vía AJAX
             async guardarQuickMarca() {
                 if (!this.quickMarcaNombre.trim()) return;
                 if (!this.idCat) {
                     this.quickMarcaError = 'Selecciona primero una categoría.';
                     return;
                 }
                 this.quickMarcaLoading = true;
                 this.quickMarcaError = '';

                 try {
                     const res = await fetch('{{ route('marcas.store') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                         },
                         body: JSON.stringify({
                             id_cat: this.idCat,
                             nombre: this.quickMarcaNombre.trim()
                         })
                     });
                     const data = await res.json();
                     if (res.ok && data.success) {
                         // Agregar a la categoría local
                         const cat = this.categorias.find(c => String(c.id_cat) === String(this.idCat));
                         if (cat) {
                             if (!cat.marcas) cat.marcas = [];
                             data.marca.modelos = [];
                             cat.marcas.push(data.marca);
                         }
                         this.idMarca = String(data.marca.id_marca);
                         this.idModelo = '';
                         this.quickMarcaOpen = false;
                         this.quickMarcaNombre = '';
                     } else {
                         this.quickMarcaError = data.message || 'Error al guardar la marca.';
                     }
                 } catch (e) {
                     this.quickMarcaError = 'Error de conexión con el servidor.';
                 } finally {
                     this.quickMarcaLoading = false;
                 }
             },

             // Guardar Modelo Rápido vía AJAX
             async guardarQuickModelo() {
                 if (!this.quickModeloNombre.trim()) return;
                 if (!this.idCat || !this.idMarca) {
                     this.quickModeloError = 'Selecciona primero categoría y marca.';
                     return;
                 }
                 this.quickModeloLoading = true;
                 this.quickModeloError = '';

                 try {
                     const res = await fetch('{{ route('modelos.store') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                         },
                         body: JSON.stringify({
                             id_cat: this.idCat,
                             id_marca: this.idMarca,
                             nombre: this.quickModeloNombre.trim()
                         })
                     });
                     const data = await res.json();
                     if (res.ok && data.success) {
                         // Agregar al modelo local
                         for (const c of this.categorias) {
                             const m = (c.marcas || []).find(marca => String(marca.id_marca) === String(this.idMarca));
                             if (m) {
                                 if (!m.modelos) m.modelos = [];
                                 m.modelos.push(data.modelo);
                             }
                         }
                         this.idModelo = String(data.modelo.id_modelo);
                         this.sugerirNombre();
                         this.quickModeloOpen = false;
                         this.quickModeloNombre = '';
                     } else {
                         this.quickModeloError = data.message || 'Error al guardar el modelo.';
                     }
                 } catch (e) {
                     this.quickModeloError = 'Error de conexión con el servidor.';
                 } finally {
                     this.quickModeloLoading = false;
                 }
             },

             async handleSerieEnter() {
                 await this.checkSerie();
                 if (this.checkStatus === 'success') {
                     this.seccion = 2;
                     this.$nextTick(() => {
                         if (this.$refs.nombreInput) {
                             this.$refs.nombreInput.focus();
                         }
                     });
                 }
             },
             async checkSerie() {
                 const s = this.serie.trim();
                 if (!s) {
                     this.checkStatus = null;
                     this.checkMessage = null;
                     return;
                 }

                 this.isChecking = true;
                 try {
                     const res = await fetch(`/productos/check-serie/${encodeURIComponent(s)}`);
                     const data = await res.json();
                     if (data.existe) {
                         window.playScannerBeep('error');
                         this.checkStatus = 'error';
                         this.checkMessage = data.mensaje;
                     } else {
                         window.playScannerBeep('success');
                         this.checkStatus = 'success';
                         this.checkMessage = 'Código libre y listo para ingresar al inventario';
                         this.renderBarcode();
                     }
                 } catch (e) {
                     this.checkStatus = null;
                 } finally {
                     this.isChecking = false;
                 }
             },
             renderBarcode() {
                 this.$nextTick(() => {
                     if (window.JsBarcode && this.$refs.barcodeSvg && this.serie.trim()) {
                         try {
                             window.JsBarcode(this.$refs.barcodeSvg, this.serie.trim(), {
                                 format: 'CODE128',
                                 lineColor: '#171717',
                                 width: 2,
                                 height: 40,
                                 displayValue: true,
                                 background: 'transparent',
                                 fontOptions: 'bold',
                                 font: 'monospace',
                                 fontSize: 12,
                                 textColor: '#171717'
                             });
                         } catch (e) {}
                     }
                 });
             }
         }">

        <form method="POST" action="{{ route('productos.store') }}" class="space-y-5">
            @csrf

            <!-- NAVEGACIÓN ENTRE SECCIONES (TABS / PASOS) -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white border border-[#E2E8F0] p-2.5 rounded-2xl shadow-xs">
                <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto py-1">
                    <!-- Sección 1 Tab -->
                    <button type="button"
                            @click="seccion = 1"
                            :class="seccion === 1 ? 'bg-[#F8FAFC] text-[#2563EB] border-[#E2E8F0] font-bold shadow-2xs' : 'text-[#64748B] hover:text-[#1E293B] border-transparent font-semibold'"
                            class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border text-xs transition-all whitespace-nowrap cursor-pointer">
                        <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[11px] font-semibold"
                              :class="seccion === 1 ? 'bg-[#2563EB] text-white' : 'bg-[#E2E8F0] text-[#64748B]'">1</span>
                        <span>Código y Serie</span>
                    </button>

                    <svg class="w-3.5 h-3.5 text-[#CBD5E1] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>

                    <!-- Sección 2 Tab -->
                    <button type="button"
                            @click="seccion = 2"
                            :class="seccion === 2 ? 'bg-[#F8FAFC] text-[#2563EB] border-[#E2E8F0] font-bold shadow-2xs' : 'text-[#64748B] hover:text-[#1E293B] border-transparent font-semibold'"
                            class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border text-xs transition-all whitespace-nowrap cursor-pointer">
                        <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[11px] font-semibold"
                              :class="seccion === 2 ? 'bg-[#2563EB] text-white' : 'bg-[#E2E8F0] text-[#64748B]'">2</span>
                        <span>Clasificación</span>
                    </button>

                    <svg class="w-3.5 h-3.5 text-[#CBD5E1] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>

                    <!-- Sección 3 Tab -->
                    <button type="button"
                            @click="seccion = 3"
                            :class="seccion === 3 ? 'bg-[#F8FAFC] text-[#2563EB] border-[#E2E8F0] font-bold shadow-2xs' : 'text-[#64748B] hover:text-[#1E293B] border-transparent font-semibold'"
                            class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border text-xs transition-all whitespace-nowrap cursor-pointer">
                        <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[11px] font-semibold"
                              :class="seccion === 3 ? 'bg-[#2563EB] text-white' : 'bg-[#E2E8F0] text-[#64748B]'">3</span>
                        <span>Ubicación</span>
                    </button>
                </div>

                <!-- Botón de Alternar Modo de Vista (Pasos vs Completo) -->
                <button type="button"
                        @click="vistaCompleta = !vistaCompleta"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span x-text="vistaCompleta ? 'Ver por pasos' : 'Ver todas las secciones'"></span>
                </button>
            </div>

            <!-- ==================== SECCIÓN 1: IDENTIFICACIÓN Y CÓDIGO ==================== -->
            <div x-show="vistaCompleta || seccion === 1"
                 x-transition.opacity
                 class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-6 sm:p-7 space-y-6">
                <!-- Encabezado de la Sección -->
                <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#EFF6FF] text-[#2563EB] font-semibold text-sm flex items-center justify-center border border-[#BFDBFE]">
                            1
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-[#1E293B]">Identificación y Código de Barras</h2>
                        </div>
                    </div>
                    <span class="text-[11px] text-[#64748B] hidden sm:flex items-center gap-1.5 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#2563EB] animate-ping"></span>
                        Listo para lector USB/Bluetooth
                    </span>
                </div>

                <!-- 1.1 Modalidad de Ingreso -->
                <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-[#1E293B]">
                            Modalidad de Ingreso
                        </span>
                    </div>
                    <div class="inline-flex p-1 rounded-xl bg-white border border-[#E2E8F0]">
                        <button type="button"
                                @click="tipo = 'unitario'; cantidad = 1"
                                :class="tipo === 'unitario' ? 'bg-[#1E293B] text-white shadow-xs font-bold' : 'text-[#64748B] hover:text-[#1E293B] font-medium'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                            <span>Individual</span>
                        </button>
                        <button type="button"
                                @click="tipo = 'lote'; if(cantidad <= 1) cantidad = 100"
                                :class="tipo === 'lote' ? 'bg-[#2563EB] text-white shadow-xs font-bold' : 'text-[#64748B] hover:text-[#1E293B] font-medium'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Lote</span>
                        </button>
                    </div>
                    <input type="hidden" name="tipo" :value="tipo" />
                </div>

                <!-- 1.2 Cantidad del Lote (Solo cuando tipo === 'lote') -->
                <div x-show="tipo === 'lote'" x-cloak x-transition class="p-4 rounded-xl bg-[#F1F5F9]/60 border border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <label for="cantidad_lote" class="block text-xs font-bold uppercase tracking-wider text-[#2563EB]">
                            Cantidad Inicial del Lote <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-[#64748B]" x-text="unidad === 'metros' ? 'Metros totales del rollo o bobina (p. ej. cable).' : 'Número de unidades físicas contenidas en el lote.'"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number"
                               id="cantidad_lote"
                               name="cantidad"
                               x-model.number="cantidad"
                               min="1"
                               max="100000"
                               class="w-32 rounded-xl bg-white border border-[#E2E8F0] text-[#1E293B] text-center font-mono font-bold text-base py-2 px-3 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB]" />
                        <select name="unidad"
                                x-model="unidad"
                                :disabled="tipo !== 'lote'"
                                aria-label="Unidad de medida del lote"
                                class="rounded-xl bg-white border border-[#E2E8F0] text-[#1E293B] text-xs font-bold py-2 pl-3 pr-8 focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] cursor-pointer">
                            <option value="unidades">unidades</option>
                            <option value="metros">metros</option>
                        </select>
                    </div>
                </div>

                <!-- 1.3 Número de Serie / Código de Barras -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="numero_serie" class="block text-xs font-bold text-[#1E293B] uppercase tracking-wider">
                            <span x-text="tipo === 'lote' ? 'Código de Lote / Código de Barras *' : 'Número de Serie / Código de Barras *'"></span>
                        </label>
                        <span class="text-[11px] text-[#64748B]">
                            Presiona <strong>Enter</strong> o <strong>Verificar</strong> tras escanear
                        </span>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#2563EB]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-4v4m0-4v-4m0 4h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <input id="numero_serie"
                               type="text"
                               name="numero_serie"
                               x-ref="serieInput"
                               x-model="serie"
                               @keydown.enter.prevent="handleSerieEnter()"
                               @blur="checkSerie()"
                               placeholder="Escanea el código con el lector o ingresa la serie..."
                               required
                               maxlength="50"
                               autocomplete="off"
                               class="w-full pl-11 pr-24 py-2.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-[#1E293B] placeholder-[#94A3B8] font-mono text-sm transition-all" />

                        <div class="absolute inset-y-0 right-2 flex items-center">
                            <button type="button"
                                    @click="handleSerieEnter()"
                                    class="px-3.5 py-1.5 rounded-xl bg-[#1E293B] hover:bg-[#334155] text-white text-xs font-semibold cursor-pointer transition-all">
                                Verificar
                            </button>
                        </div>
                    </div>

                    <!-- Mensaje de verificación -->
                    <div x-show="checkMessage" x-transition class="text-xs font-medium"
                         :class="checkStatus === 'error' ? 'text-rose-600' : 'text-emerald-700'">
                        <span x-text="checkMessage"></span>
                    </div>

                    <!-- Previsualización de Código de Barras SVG con JsBarcode -->
                    <div x-show="serie.trim().length > 0 && checkStatus !== 'error'" x-transition class="pt-3 border-t border-[#F1F5F9] flex items-center justify-between">
                        <span class="text-[11px] text-[#64748B] font-medium">Etiqueta de código de barras generada:</span>
                        <div class="bg-[#F8FAFC] px-3 py-1.5 rounded-xl border border-[#E2E8F0]">
                            <svg x-ref="barcodeSvg"></svg>
                        </div>
                    </div>

                    @error('numero_serie')
                        <p class="mt-1 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Botón de avance para Sección 1 -->
                <div x-show="!vistaCompleta" class="flex items-center justify-end pt-4 border-t border-[#F1F5F9]">
                    <button type="button"
                            @click="avanzarASeccion2()"
                            class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                        <span>Continuar a Clasificación</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ==================== SECCIÓN 2: CLASIFICACIÓN DEL EQUIPO ==================== -->
            <div x-show="vistaCompleta || seccion === 2"
                 x-transition.opacity
                 class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-6 sm:p-7 space-y-6">
                <!-- Encabezado de la Sección -->
                <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#EFF6FF] text-[#2563EB] font-semibold text-sm flex items-center justify-center border border-[#BFDBFE]">
                            2
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-[#1E293B]">Clasificación y Denominación</h2>
                        </div>
                    </div>
                </div>

                <!-- 2.1 Categoría, Marca y Modelo -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Categoría -->
                    <div>
                        <label for="id_cat" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                            Categoría <span class="text-rose-500">*</span>
                        </label>
                        <select id="id_cat"
                                name="id_cat"
                                x-model="idCat"
                                @change="onCatChange()"
                                required
                                class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2.5 text-[#1E293B] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all font-medium">
                            <option value="" disabled>Seleccione categoría...</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id_cat }}">
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_cat')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Marca -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="id_marca" class="block text-xs font-bold uppercase tracking-wider text-[#64748B]">
                                Marca
                            </label>
                            <button type="button"
                                    x-show="idCat"
                                    @click="quickMarcaOpen = true; quickMarcaError = ''"
                                    class="text-[11px] font-bold text-[#2563EB] hover:underline cursor-pointer">
                                + Nueva
                            </button>
                        </div>
                        <select id="id_marca"
                                name="id_marca"
                                x-model="idMarca"
                                @change="onMarcaChange()"
                                :disabled="!idCat"
                                class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2.5 text-[#1E293B] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all font-medium disabled:bg-[#F1F5F9] disabled:opacity-60">
                            <option value="">Seleccione marca...</option>
                            <template x-for="m in marcasDisponibles" :key="m.id_marca">
                                <option :value="m.id_marca" x-text="m.nombre" :selected="String(m.id_marca) === String(idMarca)"></option>
                            </template>
                        </select>
                        @error('id_marca')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Modelo -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="id_modelo" class="block text-xs font-bold uppercase tracking-wider text-[#64748B]">
                                Modelo
                            </label>
                            <button type="button"
                                    x-show="idMarca"
                                    @click="quickModeloOpen = true; quickModeloError = ''"
                                    class="text-[11px] font-bold text-[#2563EB] hover:underline cursor-pointer">
                                + Nuevo
                            </button>
                        </div>
                        <select id="id_modelo"
                                name="id_modelo"
                                x-model="idModelo"
                                @change="onModeloChange()"
                                :disabled="!idMarca"
                                class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2.5 text-[#1E293B] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all font-medium disabled:bg-[#F1F5F9] disabled:opacity-60">
                            <option value="">Seleccione modelo...</option>
                            <template x-for="mod in modelosDisponibles" :key="mod.id_modelo">
                                <option :value="mod.id_modelo" x-text="mod.nombre" :selected="String(mod.id_modelo) === String(idModelo)"></option>
                            </template>
                        </select>
                        @error('id_modelo')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 2.2 Nombre o Descripción del Producto y Estado -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-[#64748B]">
                            Nombre o Descripción del Equipo <span class="text-rose-500">*</span>
                        </label>
                        <span x-show="nombreAuto" class="text-[11px] text-[#2563EB] font-semibold">
                            Auto-generado a partir de categoría, marca y modelo
                        </span>
                    </div>
                    <input id="nombre"
                           type="text"
                           name="nombre"
                           x-ref="nombreInput"
                           x-model="nombre"
                           @input="nombreAuto = false"
                           placeholder="Ej. Router TP-Link Archer C6"
                           required
                           maxlength="255"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('nombre')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Estado del Equipo (Nuevo / Usado) -->
                <div>
                    <label for="condicion" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Estado <span class="text-rose-500">*</span>
                    </label>
                    <select id="condicion"
                                name="condicion"
                                required
                                class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2.5 text-[#1E293B] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all font-medium">
                            <option value="nuevo" {{ old('condicion', 'nuevo') === 'nuevo' ? 'selected' : '' }}>Nuevo</option>
                            <option value="usado" {{ old('condicion', 'nuevo') === 'usado' ? 'selected' : '' }}>Usado</option>
                        </select>
                        @error('condicion')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                </div>
                </div>

                <!-- Botones de navegación para Sección 2 -->
                <div x-show="!vistaCompleta" class="flex items-center justify-between pt-4 border-t border-[#F1F5F9]">
                    <button type="button"
                            @click="seccion = 1"
                            class="px-4 py-2 rounded-xl border border-[#E2E8F0] text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>Anterior</span>
                    </button>
                    <button type="button"
                            @click="avanzarASeccion3()"
                            class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                        <span>Continuar a Ubicación</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ==================== SECCIÓN 3: UBICACIÓN Y DESTINO ==================== -->
            <div x-show="vistaCompleta || seccion === 3"
                 x-transition.opacity
                 class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-6 sm:p-7 space-y-6">
                <!-- Encabezado de la Sección -->
                <div class="flex items-center justify-between pb-4 border-b border-[#F1F5F9]">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#EFF6FF] text-[#2563EB] font-semibold text-sm flex items-center justify-center border border-[#BFDBFE]">
                            3
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-[#1E293B]">Almacén y Procedencia</h2>
                        </div>
                    </div>
                </div>

                <!-- Estado Automático: Disponible -->
                <input type="hidden" name="estado" value="disponible" />

                <!-- 3.1 Almacén y Proveedor -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Almacén Asignado -->
                    <div>
                        <label for="id_almacen" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                            Bodega / Almacén de Destino <span class="text-rose-500">*</span>
                        </label>
                        <select id="id_almacen"
                                name="id_almacen"
                                required
                                class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">
                            <option value="" disabled {{ old('id_almacen') ? '' : 'selected' }}>Seleccione el almacén...</option>
                            @foreach ($almacenes as $alm)
                                <option value="{{ $alm->id_almacen }}" {{ old('id_almacen') == $alm->id_almacen ? 'selected' : '' }}>
                                    {{ $alm->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_almacen')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Proveedor -->
                    <div>
                        <label for="id_proveedor" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                            Proveedor <span class="text-rose-500">*</span>
                        </label>
                        <select id="id_proveedor"
                                name="id_proveedor"
                                required
                                class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">
                            <option value="" disabled {{ old('id_proveedor') ? '' : 'selected' }}>Seleccione el proveedor...</option>
                            @foreach ($proveedores as $prov)
                                <option value="{{ $prov->id_prov }}" {{ old('id_proveedor') == $prov->id_prov ? 'selected' : '' }}>
                                    {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_proveedor')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Resumen Compacto Pre-Ingreso -->
                <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-xs text-[#64748B] space-y-1.5">
                    <div class="flex items-center justify-between font-medium">
                        <span>Equipo a ingresar:</span>
                        <span class="font-bold text-[#1E293B] truncate max-w-[280px]" x-text="nombre.trim() || 'Sin definir todavía'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Serie / Código:</span>
                        <span class="font-mono font-bold text-[#2563EB]" x-text="serie.trim() || 'No asignada'"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Modalidad:</span>
                        <span class="font-semibold text-[#1E293B]" x-text="tipo === 'lote' ? ('Lote (' + cantidad + (unidad === 'metros' ? ' m' : ' unidades') + ')') : 'Equipo Individual (1 unidad)'"></span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-between pt-4 border-t border-[#F1F5F9]">
                    <div x-show="!vistaCompleta">
                        <button type="button"
                                @click="seccion = 2"
                                class="px-4 py-2 rounded-xl border border-[#E2E8F0] text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Anterior</span>
                        </button>
                    </div>
                    <div :class="vistaCompleta ? 'w-full flex items-center justify-end gap-3' : 'flex items-center gap-3 ml-auto'">
                        <a href="{{ route('productos.index') }}"
                           class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-xs font-semibold transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB] cursor-pointer">
                            Registrar Entrada de Equipo
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- ==================== MINI MODAL AL VUELO: NUEVA MARCA ==================== -->
        <div x-show="quickMarcaOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-[#1E293B]/70 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="quickMarcaOpen = false" class="relative bg-white rounded-2xl border border-[#E2E8F0] shadow-2xl max-w-sm w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-[#F1F5F9]">
                    <h3 class="text-sm font-bold text-[#1E293B]">Agregar Nueva Marca</h3>
                    <button type="button" @click="quickMarcaOpen = false" class="text-[#64748B] hover:text-[#1E293B]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1">Nombre de la Marca</label>
                        <input type="text"
                               x-model="quickMarcaNombre"
                               @keydown.enter.prevent="guardarQuickMarca()"
                               placeholder="Ej. MikroTik, ZTE, TP-Link..."
                               class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2 text-sm text-[#1E293B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />
                    </div>

                    <div x-show="quickMarcaError" class="text-xs text-rose-600 font-medium" x-text="quickMarcaError"></div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#F1F5F9]">
                    <button type="button" @click="quickMarcaOpen = false" class="px-3.5 py-1.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-[#64748B] hover:bg-[#F8FAFC]">
                        Cancelar
                    </button>
                    <button type="button"
                            @click="guardarQuickMarca()"
                            :disabled="quickMarcaLoading"
                            class="px-4 py-1.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-xs disabled:opacity-60">
                        <span x-text="quickMarcaLoading ? 'Guardando...' : 'Agregar Marca'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== MINI MODAL AL VUELO: NUEVO MODELO ==================== -->
        <div x-show="quickModeloOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-[#1E293B]/70 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="quickModeloOpen = false" class="relative bg-white rounded-2xl border border-[#E2E8F0] shadow-2xl max-w-sm w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-[#F1F5F9]">
                    <h3 class="text-sm font-bold text-[#1E293B]">Agregar Nuevo Modelo</h3>
                    <button type="button" @click="quickModeloOpen = false" class="text-[#64748B] hover:text-[#1E293B]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1">Nombre / Referencia del Modelo</label>
                        <input type="text"
                               x-model="quickModeloNombre"
                               @keydown.enter.prevent="guardarQuickModelo()"
                               placeholder="Ej. Archer C6, HG8310M..."
                               class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2 text-sm text-[#1E293B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />
                    </div>

                    <div x-show="quickModeloError" class="text-xs text-rose-600 font-medium" x-text="quickModeloError"></div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#F1F5F9]">
                    <button type="button" @click="quickModeloOpen = false" class="px-3.5 py-1.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-[#64748B] hover:bg-[#F8FAFC]">
                        Cancelar
                    </button>
                    <button type="button"
                            @click="guardarQuickModelo()"
                            :disabled="quickModeloLoading"
                            class="px-4 py-1.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-xs disabled:opacity-60">
                        <span x-text="quickModeloLoading ? 'Guardando...' : 'Agregar Modelo'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
