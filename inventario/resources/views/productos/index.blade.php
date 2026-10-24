<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Gestión de Inventario</h1>
    </x-slot>

    <div class="space-y-6"
         x-data="{
             labelModalOpen: false,
             activeLabel: { serie: '', nombre: '', categoria: '', almacen: '', tipo: 'unitario', cantidad: 1, unidad: 'unidades' },
             showLabel(serie, nombre, categoria, almacen, tipo = 'unitario', cantidad = 1, unidad = 'unidades') {
                 this.activeLabel = { serie, nombre, categoria, almacen, tipo, cantidad, unidad };
                 this.labelModalOpen = true;
                 this.$nextTick(() => {
                     if (window.JsBarcode && this.$refs.printLabelSvg) {
                         window.JsBarcode(this.$refs.printLabelSvg, serie, {
                             format: 'CODE128',
                             lineColor: '#0A0A0A',
                             width: 1.8,
                             height: 45,
                             displayValue: true,
                             font: 'monospace',
                             fontSize: 13,
                             textColor: '#0A0A0A'
                         });
                     }
                 });
             },
             printLabel() {
                 window.print();
             }
         }">

        <!-- Controls: Filters and Action Buttons -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" action="{{ route('productos.index') }}" class="flex flex-wrap items-center gap-3">
                <!-- Search Box -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           name="buscar"
                           value="{{ request('buscar') }}"
                           placeholder="Buscar serie o nombre..."
                           autocomplete="off"
                           @input.debounce.500ms="$el.form.submit()"
                           @keydown.enter.prevent="$el.form.submit()"
                           x-init="if ($el.value) { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); }"
                           class="pl-9 pr-3 py-2 rounded-xl bg-white border border-[#E2E8F0] text-[#1E293B] text-xs focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] shadow-xs w-44 sm:w-56 placeholder-[#94A3B8]" />
                </div>

                <!-- Filtro Único por Categorías -->
                <div class="relative" x-data="{ open: false }">
                    <input type="hidden" name="categoria" id="categoria-input" value="{{ request('categoria') }}">
                    <button type="button"
                            @click="open = !open"
                            @click.outside="open = false"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-[#BFDBFE] text-xs font-semibold {{ request('categoria') ? 'text-[#2563EB] border-[#BFDBFE] bg-[#EFF6FF]' : 'text-[#1E293B]' }} shadow-xs hover:border-[#2563EB] transition-all">
                        <svg class="w-4 h-4 {{ request('categoria') ? 'text-[#2563EB]' : 'text-[#64748B]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>
                            @if(request('categoria'))
                                Categoría: {{ $categorias->firstWhere('id_cat', request('categoria'))?->nombre ?? 'Seleccionada' }}
                            @else
                                Filtrar por Categoría
                            @endif
                        </span>
                        <svg class="w-3.5 h-3.5 text-[#64748B] transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Menú desplegable con las 3 categorías -->
                    <div x-show="open"
                         x-transition
                         style="display: none;"
                         class="absolute left-0 mt-2 w-48 rounded-xl bg-white border border-[#E2E8F0] shadow-xl p-1.5 z-40 space-y-0.5">
                        <button type="submit"
                                @click="document.getElementById('categoria-input').value = ''; open = false"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium {{ !request('categoria') ? 'bg-[#F8FAFC] text-[#2563EB] font-bold' : 'text-[#1E293B] hover:bg-[#F8FAFC]' }} transition-colors text-left">
                            <span>Todas las Categorías</span>
                            @if(!request('categoria'))
                                <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @endif
                        </button>
                        @foreach($categorias as $cat)
                            <button type="submit"
                                    @click="document.getElementById('categoria-input').value = '{{ $cat->id_cat }}'; open = false"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium {{ request('categoria') == $cat->id_cat ? 'bg-[#EFF6FF] text-[#2563EB] font-bold' : 'text-[#1E293B] hover:bg-[#F8FAFC]' }} transition-colors text-left">
                                <span>{{ $cat->nombre }}</span>
                                @if(request('categoria') == $cat->id_cat)
                                    <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                @if(request('buscar') || request('categoria'))
                    <a href="{{ route('productos.index') }}" class="text-xs text-[#2563EB] font-semibold hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Limpiar filtros</span>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2.5 ml-auto">
                <!-- Botón Ingresar Productos (+) -->
                <div class="relative group">
                    <a href="{{ route('productos.create') }}"
                       title="Ingresar Producto"
                       aria-label="Ingresar Producto"
                       class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white shadow-sm transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </a>
                    <!-- Tooltip al pasar el cursor -->
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-[#1E293B] text-white text-[11px] font-medium rounded-xl whitespace-nowrap border border-[#334155] shadow-xl opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200 pointer-events-none z-30">
                        Ingresar Producto
                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-[#1E293B]"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden">
            @if ($productos->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-[#1E293B]">No se encontraron productos</h3>
                    <div class="mt-6">
                        <a href="{{ route('productos.create') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] text-white text-xs font-bold hover:bg-[#1D4ED8] transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Ingresar Primer Producto</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC] text-xs font-semibold uppercase tracking-wider text-[#64748B]">
                                <th class="py-4 px-6">Serie / Código de Barras</th>
                                <th class="py-4 px-6">Producto</th>
                                <th class="py-4 px-6">Categoría</th>
                                <th class="py-4 px-6">Marca</th>
                                <th class="py-4 px-6">Modelo</th>
                                <th class="py-4 px-6">Almacén</th>
                                <th class="py-4 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9] text-sm">
                            @foreach ($productos as $producto)
                                <tr class="hover:bg-[#F8FAFC]/80 transition-colors group">
                                    <td class="py-4 px-6 font-mono text-[#2563EB] font-semibold whitespace-nowrap">
                                        {{ $producto->numero_serie }}
                                    </td>

                                    <td class="py-4 px-6">
                                        <p class="font-semibold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">{{ $producto->nombre }}</p>
                                        @if(($producto->tipo ?? 'unitario') === 'lote')
                                            <span class="inline-flex items-center gap-1 mt-1 text-[11px] font-semibold text-[#2563EB] bg-[#EFF6FF] px-2 py-0.5 rounded-md border border-[#BFDBFE]/60"
                                                  title="Lote con {{ \App\Models\Producto::formatearCantidad((int) $producto->cantidad_disponible, $producto->unidad) }} disponibles de {{ $producto->cantidad }} iniciales">
                                                <span>Lote: {{ $producto->cantidad_disponible }} {{ $producto->esMetros() ? 'm' : 'unids.' }}</span>
                                                <span class="text-[10px] text-[#64748B] font-mono">/ {{ $producto->cantidad }}</span>
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#F8FAFC] text-[#475569] border border-[#E2E8F0]">
                                            {{ $producto->categoria?->nombre ?? 'Sin cat' }}
                                        </span>
                                    </td>

                                    <!-- Marca -->
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($producto->marca)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] text-xs font-semibold">
                                                {{ $producto->marca->nombre }}
                                            </span>
                                        @else
                                            <span class="text-xs text-[#94A3B8] italic">Sin marca</span>
                                        @endif
                                    </td>

                                    <!-- Modelo -->
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($producto->modelo)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-white text-[#475569] border border-[#E2E8F0] text-xs font-medium">
                                                {{ $producto->modelo->nombre }}
                                            </span>
                                        @else
                                            <span class="text-xs text-[#94A3B8] italic">Sin modelo</span>
                                        @endif
                                    </td>

                                    <!-- Almacén -->
                                    <td class="py-4 px-6 whitespace-nowrap text-[#475569] text-xs font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>{{ $producto->almacen?->nombre ?? 'Sin almacén' }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Imprimir Etiqueta -->
                                            <button type="button"
                                                    @click="showLabel('{{ $producto->numero_serie }}', '{{ addslashes($producto->nombre) }}', '{{ $producto->categoria?->nombre ?? 'General' }}', '{{ $producto->almacen?->nombre ?? 'N/A' }}', '{{ $producto->tipo ?? 'unitario' }}', {{ $producto->cantidad ?? 1 }}, '{{ $producto->unidad ?? 'unidades' }}')"
                                                    class="p-2 rounded-xl text-[#64748B] hover:text-[#2563EB] hover:bg-[#F8FAFC] transition-colors"
                                                    title="Imprimir Etiqueta con Código de Barras">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-4v4m0-4v-4m0 4h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>

                                            <!-- Editar -->
                                            <a href="{{ route('productos.edit', $producto) }}"
                                               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] transition-colors"
                                               title="Editar producto">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <!-- Eliminar -->
                                            <form method="POST"
                                                  action="{{ route('productos.destroy', $producto) }}"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar el producto {{ $producto->nombre }} ({{ $producto->numero_serie }})?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 rounded-xl text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                        title="Eliminar producto">
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

                @if ($productos->hasPages())
                    <div class="p-4 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                        {{ $productos->links() }}
                    </div>
                @endif
            @endif
        </div>


        <!-- MODAL: Imprimir Etiqueta Adhesiva de Código de Barras -->
        <div x-show="labelModalOpen"
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#1E293B]/60 backdrop-blur-sm print:p-0 print:bg-white"
             style="display: none;">
            <div @click.away="labelModalOpen = false"
                 class="w-full max-w-sm rounded-2xl bg-white border border-[#E2E8F0] shadow-2xl p-6 sm:p-7 space-y-4 print:border-none print:shadow-none print:p-0 print:bg-white text-[#1E293B]">
                <div class="flex items-center justify-between pb-3 border-b border-[#F1F5F9] print:hidden">
                    <h3 class="text-sm font-bold text-[#1E293B]">Etiqueta de Código de Barras</h3>
                    <button type="button" @click="labelModalOpen = false" class="text-[#64748B] hover:text-[#1E293B]">&times;</button>
                </div>

                <!-- Etiqueta Adhesiva (Optimizada para pantalla e impresión térmica) -->
                <div class="bg-[#F8FAFC] text-[#1E293B] p-4 rounded-xl border border-[#E2E8F0] text-center space-y-1 shadow-xs">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[#64748B]">Inventory</div>
                    <div class="text-sm font-bold truncate text-[#1E293B]" x-text="activeLabel.nombre"></div>
                    <template x-if="activeLabel.tipo === 'lote'">
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] my-0.5">
                            <span>LOTE:</span>
                            <span x-text="activeLabel.cantidad"></span>
                            <span x-text="activeLabel.unidad === 'metros' ? 'METROS' : 'UNIDADES'"></span>
                        </div>
                    </template>
                    <div class="flex items-center justify-center py-1">
                        <svg x-ref="printLabelSvg" class="max-w-[220px]"></svg>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-[#64748B] font-medium px-2 pt-1 border-t border-[#E2E8F0]">
                        <span x-text="activeLabel.categoria"></span>
                        <span x-text="activeLabel.almacen"></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 print:hidden">
                    <button type="button" @click="labelModalOpen = false" class="px-4 py-2 rounded-xl bg-[#F8FAFC] text-[#64748B] text-xs font-semibold hover:bg-[#F1F5F9]">
                        Cerrar
                    </button>
                    <button type="button" @click="printLabel()" class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Imprimir Etiqueta</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
