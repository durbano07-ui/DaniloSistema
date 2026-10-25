<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Proveedores</h1>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
            </div>
            <a href="{{ route('proveedores.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo Proveedor</span>
            </a>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden">
            @if ($proveedores->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#F8FAFC] text-[#64748B] border border-[#E2E8F0] flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10h10zm0 0h2m4 0h1a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0016.52 6H13" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-[#1E293B]">No hay proveedores registrados</h3>
                    <div class="mt-6">
                        <a href="{{ route('proveedores.create') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Registrar Proveedor</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC] text-[11px] font-bold uppercase tracking-wider text-[#64748B]">
                                <th class="py-4 px-6">Proveedor</th>
                                <th class="py-4 px-6">Correo</th>
                                <th class="py-4 px-6">Teléfono</th>
                                <th class="py-4 px-6">Registrado por</th>
                                <th class="py-4 px-6">Productos</th>
                                <th class="py-4 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9] text-sm">
                            @foreach ($proveedores as $proveedor)
                                <tr class="hover:bg-[#F8FAFC]/80 transition-colors">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-[#1E293B]">{{ $proveedor->nombre }}</p>
                                        <span class="text-xs text-[#64748B] font-mono">ID: #{{ $proveedor->id_prov }}</span>
                                    </td>

                                    <td class="py-4 px-6 text-xs text-[#475569]">
                                        {{ $proveedor->emails->first()?->correo ?? '—' }}
                                    </td>

                                    <td class="py-4 px-6 text-xs text-[#475569] font-mono">
                                        {{ $proveedor->telefonos->first()?->numero ?? '—' }}
                                    </td>

                                    <td class="py-4 px-6 text-xs text-[#1E293B] font-semibold">
                                        {{ $proveedor->usuario?->nombre ?? '—' }}
                                    </td>

                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0]">
                                            {{ $proveedor->productos_count }} {{ $proveedor->productos_count === 1 ? 'artículo' : 'artículos' }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('proveedores.edit', $proveedor) }}"
                                           class="inline-flex p-2 rounded-xl bg-[#F8FAFC] hover:bg-[#F1F5F9] border border-[#E2E8F0] text-[#64748B] hover:text-[#1E293B] transition-all"
                                           title="Editar proveedor">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($proveedores->hasPages())
                    <div class="p-4 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                        {{ $proveedores->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
