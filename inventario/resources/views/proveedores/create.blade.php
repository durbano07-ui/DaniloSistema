<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('proveedores.index') }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Registrar Nuevo Proveedor</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-ink-200 rounded-2xl shadow-card overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-ink-100">
                <h2 class="text-base font-bold text-ink-900">Datos del Proveedor</h2>
            </div>

            <form method="POST" action="{{ route('proveedores.store') }}" class="space-y-6">
                @csrf

                @include('proveedores._campos', ['proveedor' => null])

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-ink-100">
                    <a href="{{ route('proveedores.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-ink-200 bg-white text-ink-500 hover:text-ink-900 hover:bg-ink-50 text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-600">
                        Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
