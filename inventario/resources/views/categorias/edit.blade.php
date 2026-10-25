<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('categorias.index') }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Editar Categoría</h1>
            <span class="font-mono text-xs text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200 font-semibold">
                #{{ $categoria->id_cat }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-ink-200 rounded-2xl shadow-card overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-ink-100">
                <h2 class="text-base font-bold text-ink-900">Modificar Categoría: {{ $categoria->nombre }}</h2>
                <p class="text-xs text-ink-500 mt-1">
                    Actualiza la denominación del grupo de productos en el catálogo general.
                </p>
            </div>

            <form method="POST" action="{{ route('categorias.update', $categoria) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Nombre de la Categoría -->
                <div>
                    <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                        Nombre de la Categoría <span class="text-rose-500">*</span>
                    </label>
                    <input id="nombre"
                           type="text"
                           name="nombre"
                           value="{{ old('nombre', $categoria->nombre) }}"
                           required
                           maxlength="50"
                           autofocus
                           class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all" />
                    @error('nombre')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Resumen de Existencias -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-ink-50 border border-ink-200 p-4">
                        <span class="text-xs text-ink-500 font-bold uppercase tracking-wider block">Stock Registrado</span>
                        <p class="text-lg font-semibold text-brand-600 mt-1">{{ $categoria->stock }} {{ $categoria->stock === 1 ? 'unidad' : 'unidades' }}</p>
                    </div>
                    <div class="rounded-xl bg-ink-50 border border-ink-200 p-4">
                        <span class="text-xs text-ink-500 font-bold uppercase tracking-wider block">Productos Vinculados</span>
                        <p class="text-lg font-semibold text-ink-900 mt-1">{{ $categoria->productos()->count() }} {{ $categoria->productos()->count() === 1 ? 'artículo' : 'artículos' }}</p>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-ink-100">
                    <a href="{{ route('categorias.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-ink-200 bg-white text-ink-500 hover:text-ink-900 hover:bg-ink-50 text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-600">
                        Actualizar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
