<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Gestión de Almacén</h1>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
            </div>
            <a href="{{ route('almacenes.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Bodega</span>
            </a>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden">
            @if ($almacenes->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#F8FAFC] text-[#64748B] border border-[#E2E8F0] flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-[#1E293B]">No hay almacenes registrados</h3>
                    <div class="mt-6">
                        <a href="{{ route('almacenes.create') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Crear Bodega</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC] text-[11px] font-bold uppercase tracking-wider text-[#64748B]">
                                <th class="py-4 px-6">Almacén / Bodega</th>
                                <th class="py-4 px-6">Ubicación (Ciudad / Provincia)</th>
                                <th class="py-4 px-6">Dirección</th>
                                <th class="py-4 px-6">Responsable</th>
                                <th class="py-4 px-6">Productos</th>
                                <th class="py-4 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9] text-sm">
                            @foreach ($almacenes as $almacen)
                                <tr class="hover:bg-[#F8FAFC]/80 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                <svg class="w-5 h-5 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-[#1E293B]">{{ $almacen->nombre }}</p>
                                                <span class="text-xs text-[#64748B] font-mono">ID: #{{ $almacen->id_almacen }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6">
                                        <span class="font-semibold text-[#1E293B]">
                                            {{ $almacen->direccion?->ciudad?->nombre ?? 'Sin ciudad' }}
                                        </span>
                                        <span class="block text-xs text-[#64748B]">
                                            {{ $almacen->direccion?->ciudad?->provincia ?? '' }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-6 text-[#64748B] text-xs">
                                        {{ $almacen->direccion?->calle ?? '—' }}
                                    </td>

                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-[#1E293B] text-white flex items-center justify-center text-xs font-bold">
                                                {{ strtoupper(substr($almacen->usuario?->nombre ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-[#1E293B]">{{ $almacen->usuario?->nombre ?? 'No asignado' }}</p>
                                                <span class="text-[11px] text-[#64748B] font-mono">{{ $almacen->numero_cedula }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0]">
                                            {{ $almacen->productos_count }} {{ $almacen->productos_count === 1 ? 'artículo' : 'artículos' }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('almacenes.edit', $almacen) }}"
                                               class="p-2 rounded-xl bg-[#F8FAFC] hover:bg-[#F1F5F9] border border-[#E2E8F0] text-[#64748B] hover:text-[#1E293B] transition-all"
                                               title="Editar almacén">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('almacenes.destroy', $almacen) }}"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar el almacén {{ $almacen->nombre }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 rounded-xl bg-[#F8FAFC] hover:bg-[#EFF6FF] border border-[#BFDBFE] hover:border-[#BFDBFE] text-[#64748B] hover:text-[#2563EB] transition-all"
                                                        title="Eliminar almacén">
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

                @if ($almacenes->hasPages())
                    <div class="p-4 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                        {{ $almacenes->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
