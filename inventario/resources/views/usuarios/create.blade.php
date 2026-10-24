<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Registrar Nuevo Usuario</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-[#F1F5F9]">
                <h2 class="text-base font-bold text-[#1E293B]">Datos del Personal</h2>
            </div>

            <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6">
                @csrf

                <!-- Número de Cédula -->
                <div>
                    <label for="numero_cedula" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Número de Cédula <span class="text-rose-500">*</span>
                    </label>
                    <input id="numero_cedula"
                           type="text"
                           name="numero_cedula"
                           value="{{ old('numero_cedula') }}"
                           maxlength="10"
                           placeholder="Ej. 1710034065"
                           required
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 font-mono text-sm transition-all" />
                    <p class="mt-1 text-xs text-[#64748B]">Debe contener exactamente 10 dígitos numéricos.</p>
                    @error('numero_cedula')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Nombre Completo -->
                <div>
                    <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Nombre Completo <span class="text-rose-500">*</span>
                    </label>
                    <input id="nombre"
                           type="text"
                           name="nombre"
                           value="{{ old('nombre') }}"
                           maxlength="30"
                           placeholder="Ej. Juan Pérez"
                           required
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    <p class="mt-1 text-xs text-[#64748B]">Máximo 30 caracteres.</p>
                    @error('nombre')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Rol de Acceso -->
                <div>
                    <label for="id_rol" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Rol en el Sistema <span class="text-rose-500">*</span>
                    </label>
                    <select id="id_rol"
                            name="id_rol"
                            required
                            class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">
                        <option value="" disabled {{ old('id_rol') ? '' : 'selected' }}>Seleccione un rol...</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_rol')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Contraseña de Acceso
                    </label>
                    <input id="password"
                           type="text"
                           name="password"
                           autocomplete="new-password"
                           placeholder="Déjala vacía para generarla automáticamente"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    <p class="mt-1 text-xs text-[#64748B]">Si la dejas vacía, el sistema genera una contraseña segura y te la muestra al guardar.</p>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#F1F5F9]">
                    <a href="{{ route('usuarios.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
