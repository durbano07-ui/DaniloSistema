<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Panel de Control</h1>
    </x-slot>

    <div class="space-y-8">
        <!-- Welcome Banner -->
        <div class="p-8 sm:p-10 rounded-2xl bg-white border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04)] relative overflow-hidden">
            <div class="relative z-10 max-w-2xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] mb-4">
                    Rol: {{ Auth::user()->rol?->nombre ?? 'Usuario' }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-semibold text-[#1E293B] tracking-tight">
                    Hola, {{ Auth::user()->nombre }}
                </h2>
            </div>
        </div>

        <!-- Quick Access Module Cards -->
        <div>
            <div class="mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#94A3B8]">Accesos Rápidos</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ Auth::user()->tieneRol('Almacenista') ? '3' : '4' }} gap-5">
                @if(Auth::user()->tieneRol('Almacenista'))
                    <!-- 1. Inventario -->
                    <a href="{{ route('productos.index') }}"
                       class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center mb-5 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                Inventario
                            </h4>
                        </div>
                        <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                            <span>Ir a inventario</span>
                            <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>

                    <!-- 2. Categorías -->
                    <a href="{{ route('categorias.index') }}"
                       class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] flex items-center justify-center mb-5 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                Categorías
                            </h4>
                        </div>
                        <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                            <span>Ir a categorías</span>
                            <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>

                    <!-- 3. Almacén -->
                    <a href="{{ route('almacenes.index') }}"
                       class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] flex items-center justify-center mb-5 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                Almacén
                            </h4>
                        </div>
                        <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                            <span>Ir a bodegas</span>
                            <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>

                    <!-- 4. Reportes -->
                    <a href="{{ route('reportes.index') }}"
                       class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] flex items-center justify-center mb-5 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                Reportes
                            </h4>
                        </div>
                        <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                            <span>Ir a reportes</span>
                            <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>

                @else
                    <!-- Tarjetas para Administrador -->
                    @can('gestionar-personal')
                        <a href="{{ route('usuarios.index') }}"
                           class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center mb-5 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                    Gestión de Personal
                                </h4>
                            </div>
                            <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                                <span>Ir a personal</span>
                                <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @endcan

                    @can('gestionar-almacen')
                        <a href="{{ route('almacenes.index') }}"
                           class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] flex items-center justify-center mb-5 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                    Gestión de Almacén
                                </h4>
                            </div>
                            <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                                <span>Ir a bodegas</span>
                                <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @endcan

                    @can('generar-reportes')
                        <a href="{{ route('reportes.index') }}"
                           class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-[#F8FAFC] text-[#1E293B] border border-[#E2E8F0] flex items-center justify-center mb-5 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                    Gestión de Reportes
                                </h4>
                                <p class="text-xs text-[#64748B] mt-2 leading-relaxed">
                                    Generación, auditoría y actas oficiales de inventario y personal.
                                </p>
                            </div>
                            <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                                <span>Ir a reportes</span>
                                <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @endcan

                    @can('gestionar-inventario')
                        <a href="{{ route('productos.index') }}"
                           class="group p-6 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#2563EB]/40 hover:shadow-[0_8px_24px_-8px_rgba(15,23,42,0.12)] transition-all shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center mb-5 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-[#1E293B] group-hover:text-[#2563EB] transition-colors">
                                    Gestión de Inventario
                                </h4>
                                <p class="text-xs text-[#64748B] mt-2 leading-relaxed">
                                    Catálogo de productos, stock disponible, categorías y proveedores.
                                </p>
                            </div>
                            <div class="mt-5 pt-4 border-t border-[#F1F5F9] flex items-center text-xs font-bold text-[#2563EB]">
                                <span>Ir a inventario</span>
                                <svg class="w-3.5 h-3.5 ms-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
