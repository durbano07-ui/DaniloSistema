<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Categorías de Inventario</h1>
        </div>
    </x-slot>

    @php
        $primeraCat = $categorias->first();
        $idPrimeraCat = $primeraCat ? $primeraCat->id_cat : '';
    @endphp

    <div x-data="{
        // Micropestaña / Filtro de categoría activa
        catActiva: '{{ $idPrimeraCat }}', // ID de categoría o 'todas'

        // Modal Ver Modelos (con opción de crear nuevo modelo dentro)
        modalVerModelosOpen: false,
        verMarca: null,
        verCategoriaNombre: '',
        editandoModeloId: null,
        editandoModeloNombre: '',

        // Modal Marca
        modalMarcaOpen: false,
        modalMarcaModo: 'crear', // 'crear' | 'editar'
        marcaId: null,
        marcaIdCat: '',
        marcaNombre: '',
        marcaModeloInicial: '',

        // Categorías en memoria
        categoriasData: @js($categorias),

        abrirVerModelos(marca, categoriaNombre) {
            this.verMarca = marca;
            this.verCategoriaNombre = categoriaNombre;
            this.editandoModeloId = null;
            this.editandoModeloNombre = '';
            this.modalVerModelosOpen = true;
        },

        abrirCrearMarca(idCat = '') {
            this.modalMarcaModo = 'crear';
            this.marcaId = null;
            this.marcaIdCat = (idCat && idCat !== 'todas') ? idCat : (this.catActiva !== 'todas' ? this.catActiva : (this.categoriasData.length > 0 ? this.categoriasData[0].id_cat : ''));
            this.marcaNombre = '';
            this.marcaModeloInicial = '';
            this.modalMarcaOpen = true;
        },

        abrirEditarMarca(marca) {
            this.modalMarcaModo = 'editar';
            this.marcaId = marca.id_marca;
            this.marcaIdCat = marca.id_cat;
            this.marcaNombre = marca.nombre;
            this.marcaModeloInicial = '';
            this.modalMarcaOpen = true;
        }
    }" class="space-y-6">

        <!-- Barra Superior: Filtro de Categoría y Botones de Acción -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Filtro por Categoría (desplegable) -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                            @click="open = !open"
                            @click.outside="open = false"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] text-xs font-semibold text-[#1E293B] shadow-xs hover:border-[#2563EB] transition-all">
                        <svg class="w-4 h-4" :class="catActiva !== 'todas' ? 'text-[#2563EB]' : 'text-[#64748B]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span x-show="catActiva === 'todas'">Todas las Categorías</span>
                        @foreach ($categorias as $cat)
                            <span x-show="catActiva === '{{ $cat->id_cat }}'" x-cloak>Categoría: {{ $cat->nombre }}</span>
                        @endforeach
                        <svg class="w-3.5 h-3.5 text-[#64748B] transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open"
                         x-transition
                         x-cloak
                         class="absolute left-0 mt-2 w-60 rounded-xl bg-white border border-[#E2E8F0] shadow-xl p-1.5 z-40 space-y-0.5">
                        <button type="button"
                                @click="catActiva = 'todas'; open = false"
                                :class="catActiva === 'todas' ? 'bg-[#F8FAFC] text-[#2563EB] font-bold' : 'text-[#1E293B] hover:bg-[#F8FAFC]'"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors text-left">
                            <span>Todas las Categorías</span>
                            <span class="flex items-center gap-2">
                                <span class="text-[10px] text-[#94A3B8] font-normal">{{ $categorias->sum(fn($c) => $c->marcas->count()) }} marcas</span>
                                <span x-show="catActiva === 'todas'"><svg class="w-3.5 h-3.5 text-[#2563EB]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                            </span>
                        </button>
                        @foreach ($categorias as $cat)
                            <button type="button"
                                    @click="catActiva = '{{ $cat->id_cat }}'; open = false"
                                    :class="catActiva === '{{ $cat->id_cat }}' ? 'bg-[#F8FAFC] text-[#2563EB] font-bold' : 'text-[#1E293B] hover:bg-[#F8FAFC]'"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors text-left">
                                <span>{{ $cat->nombre }}</span>
                                <span class="flex items-center gap-2">
                                    <span class="text-[10px] text-[#94A3B8] font-normal">{{ $cat->stock }} unids.</span>
                                    <span x-show="catActiva === '{{ $cat->id_cat }}'" x-cloak><svg class="w-3.5 h-3.5 text-[#2563EB]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

            <!-- Acciones de la categoría activa (Editar / Eliminar la categoría seleccionada) -->
            @foreach ($categorias as $cat)
                <div x-show="catActiva === '{{ $cat->id_cat }}'" x-cloak class="flex items-center gap-2 self-end sm:self-auto flex-shrink-0">
                    <a href="{{ route('categorias.edit', $cat) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F8FAFC] hover:bg-[#F1F5F9] border border-[#E2E8F0] text-[11px] font-bold text-[#64748B] hover:text-[#1E293B] transition-all"
                       title="Editar datos de {{ $cat->nombre }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Editar Categoría</span>
                    </a>

                    <form method="POST"
                          action="{{ route('categorias.destroy', $cat) }}"
                          onsubmit="return confirm('¿Estás seguro de eliminar la categoría «{{ $cat->nombre }}»?');"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F8FAFC] hover:bg-[#EFF6FF] border border-[#BFDBFE] hover:border-[#BFDBFE] text-[11px] font-bold text-[#64748B] hover:text-[#2563EB] transition-all cursor-pointer"
                                title="Eliminar categoría {{ $cat->nombre }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Eliminar</span>
                        </button>
                    </form>
                </div>
            @endforeach
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center gap-2.5">
                <!-- Botón Principal: Nueva Marca (con opción de crear modelo dentro) -->
                <button type="button"
                        @click="abrirCrearMarca(catActiva)"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.99] text-white text-xs font-bold shadow-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nueva Marca</span>
                </button>

                <!-- Botón Secundario: Nueva Categoría -->
                <a href="{{ route('categorias.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-[#F8FAFC] border border-[#E2E8F0] text-[#1E293B] hover:border-[#CBD5E1] text-xs font-semibold shadow-xs transition-all">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Nueva Categoría</span>
                </a>
            </div>
        </div>

        <!-- ==================== TABLA DE MARCAS Y MODELOS ==================== -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden">
            @php
                $todasLasMarcas = $categorias->flatMap(fn($c) => $c->marcas);
            @endphp

            @if ($todasLasMarcas->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#F8FAFC] text-[#64748B] border border-[#E2E8F0] flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-[#1E293B]">No hay marcas registradas</h3>
                    <p class="text-xs text-[#64748B] mt-1">Pulsa «Nueva Marca» para registrar marcas y modelos en el catálogo.</p>
                    <div class="mt-6">
                        <button type="button"
                                @click="abrirCrearMarca()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Registrar Primera Marca</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC] text-[11px] font-bold uppercase tracking-wider text-[#64748B]">
                                <th class="py-4 px-6 w-1/3">Marcas</th>
                                <th class="py-4 px-6 w-1/3">Modelos</th>
                                <th class="py-4 px-6 w-1/6">Stock Acumulado</th>
                                <th class="py-4 px-6 text-right w-1/6">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9] text-sm">
                            @foreach ($categorias as $categoria)
                                @foreach ($categoria->marcas as $marca)
                                    <tr x-show="catActiva === 'todas' || catActiva === '{{ $categoria->id_cat }}'"
                                        class="hover:bg-[#F8FAFC]/80 transition-colors align-middle">
                                        <!-- 1. Marca -->
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-center font-bold text-xs text-[#2563EB] flex-shrink-0 shadow-2xs">
                                                    {{ mb_substr($marca->nombre, 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-[#1E293B] leading-snug">{{ $marca->nombre }}</p>
                                                    <div class="flex items-center gap-1.5 mt-0.5">
                                                        <span x-show="catActiva === 'todas'" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#F8FAFC] text-[#64748B] border border-[#E2E8F0]">
                                                            {{ $categoria->nombre }}
                                                        </span>
                                                        <span class="text-[11px] text-[#94A3B8] font-mono">
                                                            {{ $marca->modelos->count() }} {{ $marca->modelos->count() === 1 ? 'modelo' : 'modelos' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- 2. Modelos (Botón Ver Modelos) -->
                                        <td class="py-4 px-6">
                                            <button type="button"
                                                    @click="abrirVerModelos({{ $marca->toJson() }}, '{{ addslashes($categoria->nombre) }}')"
                                                    class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#F8FAFC] border border-[#E2E8F0] hover:border-[#2563EB]/40 text-xs font-semibold text-[#1E293B] hover:text-[#2563EB] shadow-2xs transition-all cursor-pointer">
                                                Ver Modelos
                                            </button>
                                        </td>

                                        <!-- 3. Stock Acumulado -->
                                        <td class="py-4 px-6">
                                            @php
                                                $stockMarca = $marca->stock_acumulado;
                                            @endphp
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $stockMarca > 0 ? 'bg-[#F0FDF4] text-emerald-800 border border-[#BBF7D0]' : 'bg-[#F8FAFC] text-[#64748B] border border-[#E2E8F0]' }}">
                                                    {{ $stockMarca }} {{ $stockMarca === 1 ? 'unidad' : 'unidades' }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- 4. Acciones -->
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <!-- Botón Editar Marca -->
                                                <button type="button"
                                                        @click="abrirEditarMarca({{ $marca->toJson() }})"
                                                        class="p-2 rounded-xl bg-[#F8FAFC] hover:bg-[#F1F5F9] border border-[#E2E8F0] text-[#64748B] hover:text-[#1E293B] transition-all cursor-pointer"
                                                        title="Editar nombre de la marca">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>

                                                <!-- Botón Eliminar Marca -->
                                                <form method="POST"
                                                      action="{{ route('marcas.destroy', $marca) }}"
                                                      onsubmit="return confirm('¿Estás seguro de eliminar la marca «{{ $marca->nombre }}» y sus modelos asociados?');"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-2 rounded-xl bg-[#F8FAFC] hover:bg-[#EFF6FF] border border-[#BFDBFE] hover:border-[#BFDBFE] text-[#64748B] hover:text-[#2563EB] transition-all cursor-pointer"
                                                            title="Eliminar marca">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- ==================== MODAL VER MODELOS (Y CREAR NUEVO MODELO DENTRO) ==================== -->
        <div x-show="modalVerModelosOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-[#1E293B]/70 backdrop-blur-sm"
             style="display: none;">

            <div @click.away="modalVerModelosOpen = false"
                 class="relative bg-white rounded-2xl border border-[#E2E8F0] shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-5">

                <!-- Encabezado del Modal -->
                <div class="flex items-center justify-between pb-3 border-b border-[#F1F5F9]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center font-bold text-sm text-[#2563EB]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-[#1E293B]">
                                    Modelos de <span x-text="verMarca?.nombre"></span>
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F8FAFC] text-[#64748B] border border-[#E2E8F0]" x-text="verCategoriaNombre"></span>
                            </div>
                            <p class="text-xs text-[#64748B] mt-0.5">
                                <span x-text="(verMarca?.modelos || []).length"></span> modelos registrados para esta marca.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="modalVerModelosOpen = false" class="text-[#64748B] hover:text-[#1E293B] p-1.5 rounded-xl hover:bg-[#F8FAFC]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Formulario Rápido: Crear Nuevo Modelo dentro del Modal -->
                <div class="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2">
                    <label for="nuevo_modelo_input" class="block text-xs font-bold uppercase tracking-wider text-[#2563EB]">
                        + Agregar Nuevo Modelo
                    </label>
                    <form action="{{ route('modelos.store') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="id_cat" :value="verMarca?.id_cat" />
                        <input type="hidden" name="id_marca" :value="verMarca?.id_marca" />
                        <input id="nuevo_modelo_input"
                               type="text"
                               name="nombre"
                               placeholder="Ej. Archer AX55, HG8310M, F670L..."
                               required
                               maxlength="100"
                               class="flex-1 rounded-xl bg-white border border-[#E2E8F0] px-3.5 py-2 text-xs font-medium text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all" />
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.98] text-white text-xs font-bold shadow-xs transition-all whitespace-nowrap cursor-pointer">
                            <span>Guardar Modelo</span>
                        </button>
                    </form>
                </div>

                <!-- Listado de Modelos Existentes -->
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    <template x-for="modelo in (verMarca?.modelos || [])" :key="modelo.id_modelo">
                        <div class="p-3 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#CBD5E1] flex items-center justify-between gap-3 shadow-2xs transition-all">
                            <!-- Nombre del modelo / Edición in-situ -->
                            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                <div class="w-7 h-7 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-center text-xs font-bold text-[#2563EB] flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                </div>

                                <!-- Vista Normal -->
                                <template x-if="editandoModeloId !== modelo.id_modelo">
                                    <span class="font-bold text-xs text-[#1E293B] truncate" x-text="modelo.nombre"></span>
                                </template>

                                <!-- Modo Edición Rápida -->
                                <template x-if="editandoModeloId === modelo.id_modelo">
                                    <form :action="'{{ url('modelos') }}/' + modelo.id_modelo" method="POST" class="flex items-center gap-2 flex-1">
                                        @csrf
                                        <input type="hidden" name="_method" value="PUT">
                                        <input type="text"
                                               name="nombre"
                                               x-model="editandoModeloNombre"
                                               required
                                               maxlength="100"
                                               class="flex-1 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3 py-1 text-xs text-[#1E293B] focus:border-[#2563EB]" />
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-[11px] font-bold cursor-pointer">Guardar</button>
                                        <button type="button" @click="editandoModeloId = null" class="px-2 py-1 text-[11px] text-[#64748B] hover:text-[#1E293B] cursor-pointer">Cancelar</button>
                                    </form>
                                </template>
                            </div>

                            <!-- Acciones del modelo (cuando no se está editando) -->
                            <template x-if="editandoModeloId !== modelo.id_modelo">
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button type="button"
                                            @click="editandoModeloId = modelo.id_modelo; editandoModeloNombre = modelo.nombre"
                                            class="p-1.5 rounded-lg text-[#94A3B8] hover:text-[#1E293B] hover:bg-[#F8FAFC] transition-colors cursor-pointer"
                                            title="Editar modelo">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <form :action="'{{ url('modelos') }}/' + modelo.id_modelo"
                                          method="POST"
                                          onsubmit="return confirm('¿Estás seguro de eliminar este modelo?');"
                                          class="inline">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-[#94A3B8] hover:text-[#2563EB] hover:bg-[#EFF6FF] transition-colors cursor-pointer"
                                                title="Eliminar modelo">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Estado Vacío -->
                    <template x-if="!verMarca?.modelos || verMarca.modelos.length === 0">
                        <div class="py-8 text-center text-xs text-[#64748B] bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                            No hay modelos registrados para esta marca todavía.<br>Agrega el primer modelo usando el campo de arriba.
                        </div>
                    </template>
                </div>

                <!-- Pie del Modal -->
                <div class="pt-3 border-t border-[#F1F5F9] flex justify-end">
                    <button type="button"
                            @click="modalVerModelosOpen = false"
                            class="px-5 py-2 rounded-xl bg-[#1E293B] hover:bg-[#334155] text-white text-xs font-bold transition-all cursor-pointer">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== MODAL REGISTRAR / EDITAR MARCA (CON MODELO DENTRO) ==================== -->
        <div x-show="modalMarcaOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-[#1E293B]/70 backdrop-blur-sm"
             style="display: none;">

            <div @click.away="modalMarcaOpen = false"
                 class="relative bg-white rounded-2xl border border-[#E2E8F0] shadow-2xl max-w-md w-full p-6 sm:p-8 space-y-5">

                <div class="flex items-center justify-between pb-3 border-b border-[#F1F5F9]">
                    <div>
                        <h3 class="text-base font-bold text-[#1E293B]" x-text="modalMarcaModo === 'crear' ? 'Registrar Nueva Marca' : 'Editar Nombre de Marca'"></h3>
                        <p class="text-xs text-[#64748B] mt-0.5" x-show="modalMarcaModo === 'crear'">
                            Ingresa la marca y su primer modelo de forma opcional.
                        </p>
                    </div>
                    <button type="button" @click="modalMarcaOpen = false" class="text-[#64748B] hover:text-[#1E293B]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="modalMarcaModo === 'crear' ? '{{ route('marcas.store') }}' : '{{ url('marcas') }}/' + marcaId"
                      method="POST"
                      class="space-y-4">
                    @csrf
                    <template x-if="modalMarcaModo === 'editar'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Categoría (Pre-seleccionada según la micropestaña activa) -->
                    <div>
                        <label for="modal_marca_id_cat" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                            Categoría Asignada <span class="text-rose-500">*</span>
                        </label>
                        <select id="modal_marca_id_cat"
                                name="id_cat"
                                x-model="marcaIdCat"
                                :disabled="modalMarcaModo === 'editar'"
                                required
                                class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-sm text-[#1E293B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 disabled:opacity-60">
                            <option value="" disabled>Seleccione categoría...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_cat }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nombre de la Marca -->
                    <div>
                        <label for="modal_marca_nombre" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                            Nombre de la Marca <span class="text-rose-500">*</span>
                        </label>
                        <input id="modal_marca_nombre"
                               type="text"
                               name="nombre"
                               x-model="marcaNombre"
                               placeholder="Ej. TP-Link, Huawei, MikroTik, ZTE..."
                               required
                               maxlength="100"
                               class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-sm text-[#1E293B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />
                    </div>

                    <!-- Modelo Inicial (Crear modelo directamente dentro de la marca) -->
                    <template x-if="modalMarcaModo === 'crear'">
                        <div class="p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="modal_marca_modelo" class="block text-xs font-bold uppercase tracking-wider text-[#2563EB]">
                                    Modelo Inicial (Opcional)
                                </label>
                                <span class="text-[10px] text-[#94A3B8]">Puedes crearlo aquí directamente</span>
                            </div>
                            <input id="modal_marca_modelo"
                                   type="text"
                                   name="modelo"
                                   x-model="marcaModeloInicial"
                                   placeholder="Ej. Archer C6, HG8310M, F670L..."
                                   maxlength="100"
                                   class="w-full rounded-xl bg-white border border-[#E2E8F0] px-3.5 py-2 text-sm text-[#1E293B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />
                        </div>
                    </template>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F1F5F9]">
                        <button type="button"
                                @click="modalMarcaOpen = false"
                                class="px-4 py-2 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold text-[#64748B] hover:bg-[#F8FAFC] cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm cursor-pointer">
                            <span x-text="modalMarcaModo === 'crear' ? 'Guardar Marca y Modelo' : 'Actualizar Marca'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
