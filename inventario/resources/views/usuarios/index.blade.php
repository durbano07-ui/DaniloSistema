<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Gestión de Personal</h1>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Credenciales generadas (se muestran una sola vez) -->
        @if (session('credenciales'))
            @php $cred = session('credenciales'); @endphp
            <div class="p-5 rounded-2xl bg-white border border-brand-200 shadow-card"
                 x-data="{ copiado: false, copiar() { navigator.clipboard.writeText(this.$refs.datos.innerText).then(() => { this.copiado = true; setTimeout(() => this.copiado = false, 2000); }); } }">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-ink-950">Credenciales de acceso de {{ $cred['nombre'] }}</p>
                        <p class="text-xs text-ink-500 mt-0.5">Entrégalas al usuario. Por seguridad, la contraseña no se volverá a mostrar.</p>
                        <div x-ref="datos" class="mt-3 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                            <span class="text-ink-500">Cédula:</span>
                            <span class="font-mono font-semibold text-ink-900">{{ $cred['cedula'] }}</span>
                            <span class="text-ink-500">Contraseña:</span>
                            <span class="font-mono font-semibold text-brand-600 tracking-wide">{{ $cred['password'] }}</span>
                        </div>
                    </div>
                    <button type="button"
                            @click="copiar()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-ink-200 bg-white hover:border-brand-600 text-xs font-bold text-ink-900 hover:text-brand-600 transition-all whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span x-text="copiado ? 'Copiado' : 'Copiar'">Copiar</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- Top bar with description and CTA -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
            </div>
            <a href="{{ route('usuarios.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo Usuario</span>
            </a>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white border border-ink-200 rounded-2xl shadow-card overflow-hidden">
            @if ($usuarios->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-ink-50 text-ink-500 border border-ink-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-ink-900">No hay usuarios registrados</h3>
                    <div class="mt-6">
                        <a href="{{ route('usuarios.create') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Crear Usuario</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="tabla-responsiva w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-ink-200 bg-ink-50 text-[11px] font-bold uppercase tracking-wider text-ink-500">
                                <th class="py-4 px-6">Usuario</th>
                                <th class="py-4 px-6">Cédula</th>
                                <th class="py-4 px-6">Rol de Acceso</th>
                                <th class="py-4 px-6">Estado</th>
                                <th class="py-4 px-6">Fecha Registro</th>
                                <th class="py-4 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 text-sm">
                            @foreach ($usuarios as $usuario)
                                <tr class="hover:bg-ink-50/80 transition-colors">
                                    <!-- Nombre y Avatar -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-ink-900 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                                {{ strtoupper(substr($usuario->nombre, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-ink-900">{{ $usuario->nombre }}</p>
                                                @if (Auth::user()->numero_cedula === $usuario->numero_cedula)
                                                    <span class="text-xs text-brand-600 font-semibold">(Tú)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Cédula -->
                                    <td class="py-4 px-6 font-mono text-ink-500 text-xs">
                                        {{ $usuario->numero_cedula }}
                                    </td>

                                    <!-- Rol -->
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-ink-50 text-ink-900 border border-ink-200">
                                            {{ $usuario->rol?->nombre ?? 'Sin Rol' }}
                                        </span>
                                    </td>

                                    <!-- Estado -->
                                    <td class="py-4 px-6">
                                        @if ($usuario->estado)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Fecha -->
                                    <td class="py-4 px-6 text-ink-500 text-xs font-mono">
                                        {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '—' }}
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Editar -->
                                            <a href="{{ route('usuarios.edit', $usuario) }}"
                                               class="p-2 rounded-xl bg-ink-50 hover:bg-ink-100 border border-ink-200 text-ink-500 hover:text-ink-900 transition-all"
                                               title="Editar usuario">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <!-- Eliminar -->
                                            @can('eliminar-usuario', $usuario)
                                                <form method="POST"
                                                      action="{{ route('usuarios.destroy', $usuario) }}"
                                                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar al usuario {{ $usuario->nombre }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-2 rounded-xl bg-ink-50 hover:bg-rose-50 border border-ink-200 hover:border-rose-200 text-ink-500 hover:text-rose-600 transition-all"
                                                            title="Eliminar usuario">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
