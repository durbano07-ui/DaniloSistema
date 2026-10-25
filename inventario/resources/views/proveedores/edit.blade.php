<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('proveedores.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Editar Proveedor</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-[#F1F5F9]">
                <h2 class="text-base font-bold text-[#1E293B]">{{ $proveedor->nombre }}</h2>
                <span class="text-xs text-[#64748B] font-mono">ID: #{{ $proveedor->id_prov }}</span>
            </div>

            <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('proveedores._campos', ['proveedor' => $proveedor])

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#F1F5F9]">
                    <a href="{{ route('proveedores.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
