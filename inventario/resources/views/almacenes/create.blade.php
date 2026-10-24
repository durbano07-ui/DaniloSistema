<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('almacenes.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Registrar Nuevo Almacén</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-[#F1F5F9]">
                <h2 class="text-base font-bold text-[#1E293B]">Datos de la Bodega</h2>
            </div>

            <form method="POST" action="{{ route('almacenes.store') }}" class="space-y-6">
                @csrf

                <!-- Nombre del Almacén -->
                <div>
                    <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Nombre del Almacén / Bodega <span class="text-rose-500">*</span>
                    </label>
                    <input id="nombre"
                           type="text"
                           name="nombre"
                           value="{{ old('nombre') }}"
                           placeholder="Ej. Bodega Central Guaranda"
                           required
                           maxlength="50"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('nombre')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Ciudad / Provincia -->
                <div>
                    <label for="id_ciudad" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Ciudad y Provincia <span class="text-rose-500">*</span>
                    </label>
                    <select id="id_ciudad"
                            name="id_ciudad"
                            required
                            class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">
                        <option value="" disabled {{ old('id_ciudad') ? '' : 'selected' }}>Seleccione la ciudad...</option>
                        @foreach ($ciudades as $ciudad)
                            <option value="{{ $ciudad->id_ciudad }}" {{ old('id_ciudad') == $ciudad->id_ciudad ? 'selected' : '' }}>
                                {{ $ciudad->nombre }} ({{ $ciudad->provincia }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_ciudad')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Dirección Física / Calle -->
                <div>
                    <label for="calle" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Dirección / Calle y Referencia <span class="text-rose-500">*</span>
                    </label>
                    <input id="calle"
                           type="text"
                           name="calle"
                           value="{{ old('calle') }}"
                           placeholder="Ej. Av. Guayaquil y Manabí, Km 2"
                           required
                           maxlength="100"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('calle')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Responsable de Bodega -->
                <div class="rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#1E293B] text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->nombre ?? 'U', 0, 1)) }}
                        </div>
                        <div class="text-xs">
                            <span class="text-[#64748B] font-medium">Responsable asignado (sesión actual):</span>
                            <p class="font-bold text-[#1E293B]">{{ auth()->user()->nombre }} <span class="text-[#64748B] font-mono">({{ auth()->user()->numero_cedula }})</span></p>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#F1F5F9]">
                    <a href="{{ route('almacenes.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        Guardar Bodega
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
