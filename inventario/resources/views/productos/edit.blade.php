<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('productos.index') }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Editar Producto</h1>
            <span class="font-mono text-xs text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200 font-semibold">
                {{ $producto->numero_serie }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto"
         x-data="{
             categorias: @js($categorias),
             idCat: '{{ old('id_cat', $producto->id_cat) }}',
             idMarca: '{{ old('id_marca', $producto->id_marca) }}',
             idModelo: '{{ old('id_modelo', $producto->id_modelo) }}',

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
             },

             onMarcaChange() {
                 this.idModelo = '';
             }
         }">
        <div class="bg-white border border-ink-200 rounded-2xl shadow-card overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-ink-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-ink-900">Modificar: {{ $producto->nombre }}</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('productos.update', $producto) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Número de Serie (Inmutable) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-ink-400 mb-1.5">
                            Número de Serie (Código Único Inmutable)
                        </label>
                        <div class="mt-1.5">
                            <input type="text"
                                   value="{{ $producto->numero_serie }}"
                                   disabled
                                   class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-500 font-mono text-sm cursor-not-allowed select-none" />
                        </div>
                    </div>

                    <!-- Nombre del Producto -->
                    <div>
                        <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                            Nombre o Descripción <span class="text-rose-500">*</span>
                        </label>
                        <input id="nombre"
                               type="text"
                               name="nombre"
                               value="{{ old('nombre', $producto->nombre) }}"
                               required
                               maxlength="255"
                               class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all" />
                        @error('nombre')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Estado Actual -->
                    <div>
                        <label for="estado" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                            Estado del Producto <span class="text-rose-500">*</span>
                        </label>
                        <select id="estado"
                                name="estado"
                                required
                                class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all">
                            <option value="disponible" {{ old('estado', $producto->estado) === 'disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="consumido" {{ old('estado', $producto->estado) === 'consumido' ? 'selected' : '' }}>Consumido</option>
                            <option value="averiado" {{ old('estado', $producto->estado) === 'averiado' ? 'selected' : '' }}>Averiado</option>
                            <option value="en_reparacion" {{ old('estado', $producto->estado) === 'en_reparacion' ? 'selected' : '' }}>En Reparación</option>
                        </select>
                        @error('estado')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Condición del Equipo (Nuevo / Usado) -->
                    <div>
                        <label for="condicion" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                            Condición <span class="text-rose-500">*</span>
                        </label>
                        <select id="condicion"
                                name="condicion"
                                required
                                class="w-full rounded-xl bg-ink-50 border border-ink-200 px-3.5 py-2.5 text-ink-900 focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all font-medium">
                            <option value="nuevo" {{ old('condicion', $producto->condicion ?? 'nuevo') === 'nuevo' ? 'selected' : '' }}>Nuevo</option>
                            <option value="usado" {{ old('condicion', $producto->condicion ?? 'nuevo') === 'usado' ? 'selected' : '' }}>Usado</option>
                        </select>
                        @error('condicion')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Categoría, Marca y Modelo -->
                    <div class="sm:col-span-2 p-5 rounded-2xl bg-ink-50/70 border border-ink-200 space-y-4">
                        <span class="block text-xs font-bold uppercase tracking-wider text-ink-900">
                            Clasificación del Equipo
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Categoría -->
                            <div>
                                <label for="id_cat" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                                    Categoría <span class="text-rose-500">*</span>
                                </label>
                                <select id="id_cat"
                                        name="id_cat"
                                        x-model="idCat"
                                        @change="onCatChange()"
                                        required
                                        class="w-full rounded-xl bg-white border border-ink-200 px-3.5 py-2.5 text-ink-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all font-medium">
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
                                <label for="id_marca" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                                    Marca
                                </label>
                                <select id="id_marca"
                                        name="id_marca"
                                        x-model="idMarca"
                                        @change="onMarcaChange()"
                                        :disabled="!idCat"
                                        class="w-full rounded-xl bg-white border border-ink-200 px-3.5 py-2.5 text-ink-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all font-medium disabled:bg-ink-100 disabled:opacity-60">
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
                                <label for="id_modelo" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                                    Modelo
                                </label>
                                <select id="id_modelo"
                                        name="id_modelo"
                                        x-model="idModelo"
                                        :disabled="!idMarca"
                                        class="w-full rounded-xl bg-white border border-ink-200 px-3.5 py-2.5 text-ink-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all font-medium disabled:bg-ink-100 disabled:opacity-60">
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
                    </div>

                    <!-- Almacén Asignado -->
                    <div>
                        <label for="id_almacen" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                            Bodega / Almacén de Destino <span class="text-rose-500">*</span>
                        </label>
                        <select id="id_almacen"
                                name="id_almacen"
                                required
                                class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all">
                            @foreach ($almacenes as $alm)
                                <option value="{{ $alm->id_almacen }}" {{ old('id_almacen', $producto->id_almacen) == $alm->id_almacen ? 'selected' : '' }}>
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

                    @if($producto->tipo === 'lote')
                        <!-- Ajuste de Cantidades del Lote -->
                        <div class="sm:col-span-2 p-5 rounded-xl bg-ink-100/60 border border-ink-200 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="unidad" class="block text-xs font-bold uppercase tracking-wider text-brand-600 mb-1">
                                    Unidad de Medida
                                </label>
                                <select id="unidad"
                                        name="unidad"
                                        class="w-full rounded-xl bg-white border border-ink-200 px-4 py-2.5 text-ink-900 text-sm font-bold focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 cursor-pointer">
                                    <option value="unidades" @selected(old('unidad', $producto->unidad) !== 'metros')>Unidades</option>
                                    <option value="metros" @selected(old('unidad', $producto->unidad) === 'metros')>Metros (cable)</option>
                                </select>
                                <span class="text-[11px] text-ink-500 mt-1 block">Cómo se cuenta este lote.</span>
                            </div>
                            <div>
                                <label for="cantidad" class="block text-xs font-bold uppercase tracking-wider text-brand-600 mb-1">
                                    Cantidad Inicial del Lote <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       id="cantidad"
                                       name="cantidad"
                                       value="{{ old('cantidad', $producto->cantidad) }}"
                                       min="1"
                                       class="w-full rounded-xl bg-white border border-ink-200 px-4 py-2.5 text-ink-900 font-mono text-sm font-bold focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" />
                                <span class="text-[11px] text-ink-500 mt-1 block">Total recibido originalmente en este lote (unidades o metros).</span>
                            </div>
                            <div>
                                <label for="cantidad_disponible" class="block text-xs font-bold uppercase tracking-wider text-brand-600 mb-1">
                                    Stock Disponible en Bodega <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       id="cantidad_disponible"
                                       name="cantidad_disponible"
                                       value="{{ old('cantidad_disponible', $producto->cantidad_disponible) }}"
                                       min="0"
                                       class="w-full rounded-xl bg-white border border-ink-200 px-4 py-2.5 text-ink-900 font-mono text-sm font-bold focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" />
                                <span class="text-[11px] text-ink-500 mt-1 block">Cantidad real disponible para despacho (unidades o metros).</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Proveedor -->
                <div>
                    <label for="id_proveedor" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                        Proveedor <span class="text-rose-500">*</span>
                    </label>
                    <select id="id_proveedor"
                            name="id_proveedor"
                            required
                            class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all">
                        @foreach ($proveedores as $prov)
                            <option value="{{ $prov->id_prov }}" {{ old('id_proveedor', $producto->id_proveedor) == $prov->id_prov ? 'selected' : '' }}>
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

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-ink-100">
                    <a href="{{ route('productos.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-ink-200 bg-white text-ink-500 hover:text-ink-900 hover:bg-ink-50 text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-600">
                        Actualizar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
