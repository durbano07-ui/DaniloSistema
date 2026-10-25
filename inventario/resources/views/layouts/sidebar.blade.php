@php
    $usuario = Auth::user();
    $inicio = $usuario->tieneRol('Tecnico') ? route('consumos.index') : ($usuario->tieneRol('Almacenista') ? route('productos.index') : route('dashboard'));
    $iconos = [
        'panel' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'inventario' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'salidas' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
        'categorias' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        'almacen' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'proveedores' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10h10zm0 0h2m4 0h1a1 1 0 001-1v-3.65a1 1 0 00-.22-.624l-3.48-4.35A1 1 0 0016.52 6H13',
        'personal' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'reportes' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    ];
@endphp

<aside class="flex flex-col w-64 h-full bg-white border-r border-ink-200">
    <!-- Marca -->
    <div class="h-16 flex items-center px-5 border-b border-ink-100">
        <a href="{{ $inicio }}" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-800 flex items-center justify-center text-white shadow-brand-glow ring-1 ring-brand-900/10">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['inventario'] }}" />
                </svg>
            </div>
            <div class="leading-tight">
                <span class="block text-[15px] font-semibold text-ink-950 tracking-tight">{{ config('app.name', 'Inventory') }}</span>
                <span class="block text-[11px] text-ink-500">Control de Inventario</span>
            </div>
        </a>
    </div>

    <!-- Navegación -->
    <nav class="flex-1 px-3 py-5 overflow-y-auto" aria-label="Menú principal">
        @if ($usuario->tieneRol('Almacenista'))
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Operación</p>
            <div class="space-y-0.5">
                <x-sidebar-link :href="route('productos.index')" :active="request()->routeIs('productos.*')" :icon="$iconos['inventario']">Inventario</x-sidebar-link>
                <x-sidebar-link :href="route('consumos.index')" :active="request()->routeIs('consumos.*')" :icon="$iconos['salidas']">Salida de Equipos</x-sidebar-link>
                <x-sidebar-link :href="route('categorias.index')" :active="request()->routeIs('categorias.*')" :icon="$iconos['categorias']">Categorías</x-sidebar-link>
                <x-sidebar-link :href="route('almacenes.index')" :active="request()->routeIs('almacenes.*')" :icon="$iconos['almacen']">Almacén</x-sidebar-link>
                <x-sidebar-link :href="route('proveedores.index')" :active="request()->routeIs('proveedores.*')" :icon="$iconos['proveedores']">Proveedores</x-sidebar-link>
            </div>

            <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Gestión</p>
            <div class="space-y-0.5">
                {{-- El Almacenista solo gestiona Técnicos --}}
                <x-sidebar-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')" :icon="$iconos['personal']">Técnicos</x-sidebar-link>
                <x-sidebar-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')" :icon="$iconos['reportes']">Reportes</x-sidebar-link>
            </div>

        @elseif ($usuario->tieneRol('Tecnico'))
            <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Operación</p>
            <div class="space-y-0.5">
                <x-sidebar-link :href="route('consumos.index')" :active="request()->routeIs('consumos.*')" :icon="$iconos['salidas']">Salida de Equipos</x-sidebar-link>
            </div>

        @else
            <div class="space-y-0.5">
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" :icon="$iconos['panel']">Panel</x-sidebar-link>
            </div>

            @can('gestionar-inventario')
                <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Operación</p>
                <div class="space-y-0.5">
                    <x-sidebar-link :href="route('productos.index')" :active="request()->routeIs('productos.*')" :icon="$iconos['inventario']">Inventario</x-sidebar-link>
                    <x-sidebar-link :href="route('consumos.index')" :active="request()->routeIs('consumos.*')" :icon="$iconos['salidas']">Salida de Equipos</x-sidebar-link>
                    <x-sidebar-link :href="route('categorias.index')" :active="request()->routeIs('categorias.*')" :icon="$iconos['categorias']">Categorías</x-sidebar-link>
                    <x-sidebar-link :href="route('proveedores.index')" :active="request()->routeIs('proveedores.*')" :icon="$iconos['proveedores']">Proveedores</x-sidebar-link>
                </div>
            @endcan

            <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Administración</p>
            <div class="space-y-0.5">
                @can('gestionar-personal')
                    <x-sidebar-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')" :icon="$iconos['personal']">Personal</x-sidebar-link>
                @endcan
                @can('gestionar-almacen')
                    <x-sidebar-link :href="route('almacenes.index')" :active="request()->routeIs('almacenes.*')" :icon="$iconos['almacen']">Almacenes</x-sidebar-link>
                @endcan
                @can('generar-reportes')
                    <x-sidebar-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')" :icon="$iconos['reportes']">Reportes</x-sidebar-link>
                @endcan
            </div>
        @endif
    </nav>

    <!-- Usuario y cierre de sesión -->
    <div class="p-3 border-t border-ink-100">
        <div class="flex items-center gap-3 p-2 rounded-xl">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-ink-700 to-ink-900 text-white flex items-center justify-center text-xs font-semibold flex-shrink-0">
                {{ mb_strtoupper(mb_substr($usuario->nombre ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1 leading-tight">
                <p class="text-[13px] font-semibold text-ink-900 truncate">{{ $usuario->nombre }}</p>
                <p class="text-[11px] text-ink-500 truncate">{{ $usuario->rol?->nombre ?? 'Usuario' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        title="Cerrar sesión"
                        class="p-2 rounded-lg text-ink-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                    <span class="sr-only">Cerrar sesión</span>
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['salidas'] }}" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
