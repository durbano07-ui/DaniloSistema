<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('categorias.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Editar Categoría</h1>
            <span class="font-mono text-xs text-[#2563EB] bg-[#EFF6FF] px-3 py-1 rounded-full border border-[#BFDBFE] font-semibold">
                #{{ $categoria->id_cat }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-[#F1F5F9]">
                <h2 class="text-base font-bold text-[#1E293B]">Modificar Categoría: {{ $categoria->nombre }}</h2>
                <p class="text-xs text-[#64748B] mt-1">
                    Actualiza la denominación del grupo de productos en el catálogo general.
                </p>
            </div>

            <form method="POST" action="{{ route('categorias.update', $categoria) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Nombre de la Categoría -->
                <div>
                    <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Nombre de la Categoría <span class="text-rose-500">*</span>
                    </label>
                    <input id="nombre"
                           type="text"
                           name="nombre"
                           value="{{ old('nombre', $categoria->nombre) }}"
                           required
                           maxlength="50"
                           autofocus
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('nombre')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Resumen de Existencias -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] p-4">
                        <span class="text-xs text-[#64748B] font-bold uppercase tracking-wider block">Stock Registrado</span>
                        <p class="text-lg font-semibold text-[#2563EB] mt-1">{{ $categoria->stock }} {{ $categoria->stock === 1 ? 'unidad' : 'unidades' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] p-4">
                        <span class="text-xs text-[#64748B] font-bold uppercase tracking-wider block">Productos Vinculados</span>
                        <p class="text-lg font-semibold text-[#1E293B] mt-1">{{ $categoria->productos()->count() }} {{ $categoria->productos()->count() === 1 ? 'artículo' : 'artículos' }}</p>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#F1F5F9]">
                    <a href="{{ route('categorias.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        Actualizar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
