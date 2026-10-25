<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.index') }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Editar Usuario</h1>
            <span class="font-mono text-xs text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200 font-semibold">
                {{ $usuario->numero_cedula }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-ink-200 rounded-2xl shadow-card overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-ink-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-ink-900">Modificar Datos de {{ $usuario->nombre }}</h2>
                </div>
                <div>
                    @if ($usuario->estado)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            Cuenta Activa
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                            Cuenta Bloqueada
                        </span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Número de Cédula (Inmutable) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink-400 mb-1.5">
                        Número de Cédula (Identificador Único)
                    </label>
                    <input type="text"
                           value="{{ $usuario->numero_cedula }}"
                           disabled
                           class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-500 font-mono text-sm cursor-not-allowed select-none" />
                    <p class="mt-1 text-xs text-ink-400">La cédula no puede modificarse por ser clave primaria del sistema.</p>
                </div>

                <!-- Nombre Completo -->
                <div>
                    <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                        Nombre Completo <span class="text-rose-500">*</span>
                    </label>
                    <input id="nombre"
                           type="text"
                           name="nombre"
                           value="{{ old('nombre', $usuario->nombre) }}"
                           maxlength="30"
                           required
                           placeholder="Ej. Juan Pérez"
                           class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all" />
                    <p class="mt-1 text-xs text-ink-500">Máximo 30 caracteres.</p>
                    @error('nombre')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Rol de Acceso -->
                <div>
                    <label for="id_rol" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                        Rol en el Sistema <span class="text-rose-500">*</span>
                    </label>
                    <select id="id_rol"
                            name="id_rol"
                            required
                            class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ (old('id_rol', $usuario->id_rol) == $rol->id_rol) ? 'selected' : '' }}>
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

                <!-- Estado de la Cuenta (FA-2: Bloqueo de cuentas inactivas) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-2">
                        Estado de la Cuenta
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ old('estado', $usuario->estado) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-ink-50 border-ink-200 text-ink-500 hover:border-ink-300' }}">
                            <input type="radio"
                                   name="estado"
                                   value="1"
                                   {{ old('estado', $usuario->estado) ? 'checked' : '' }}
                                   class="text-brand-600 focus:ring-brand-600 bg-white border-ink-200 mr-3">
                            <div>
                                <span class="block text-xs font-bold text-ink-900">Activo</span>
                                <span class="block text-[11px] text-ink-500">Permite iniciar sesión en el sistema</span>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ !old('estado', $usuario->estado) ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-ink-50 border-ink-200 text-ink-500 hover:border-ink-300' }}">
                            <input type="radio"
                                   name="estado"
                                   value="0"
                                   {{ !old('estado', $usuario->estado) ? 'checked' : '' }}
                                   class="text-brand-600 focus:ring-brand-600 bg-white border-ink-200 mr-3">
                            <div>
                                <span class="block text-xs font-bold text-ink-900">Inactivo</span>
                                <span class="block text-[11px] text-ink-500">Bloquea el acceso al sistema (FA-2)</span>
                            </div>
                        </label>
                    </div>
                    @error('estado')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Nueva Contraseña (Opcional) -->
                <div class="pt-4 border-t border-ink-100">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                        Cambiar Contraseña <span class="text-xs text-ink-400 font-normal lowercase">(opcional)</span>
                    </label>
                    <input id="password"
                           type="password"
                           name="password"
                           placeholder="Dejar en blanco para mantener la contraseña actual"
                           class="w-full rounded-xl bg-ink-50 border border-ink-200 px-4 py-2.5 text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 text-sm transition-all" />
                    <p class="mt-1 text-xs text-ink-500">Solo completa este campo si deseas cambiar la contraseña del usuario (mínimo 8 caracteres).</p>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-ink-100">
                    <a href="{{ route('usuarios.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-ink-200 bg-white text-ink-500 hover:text-ink-900 hover:bg-ink-50 text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-600">
                        Actualizar Usuario
                    </button>
                </div>
            </form>

            <!-- Restablecer Contraseña (genera una nueva automáticamente) -->
            <form method="POST"
                  action="{{ route('usuarios.restablecer-password', $usuario) }}"
                  onsubmit="return confirm('¿Generar una nueva contraseña para {{ $usuario->nombre }}?\n\nLa contraseña actual dejará de funcionar.');"
                  class="mt-8 pt-6 border-t border-ink-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                @csrf
                <div>
                    <p class="text-sm font-semibold text-ink-900">Restablecer contraseña</p>
                    <p class="text-xs text-ink-500 mt-0.5">Genera una contraseña nueva y te la muestra para entregarla al usuario.</p>
                </div>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-ink-200 bg-white hover:border-brand-600 text-ink-900 hover:text-brand-600 text-xs font-bold transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>Generar nueva contraseña</span>
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
