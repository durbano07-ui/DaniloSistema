<aside class="flex flex-col w-64 bg-[#1E293B] border-r border-[#334155] text-[#CBD5E1] h-full">
    <!-- Brand / Logo -->
    <div class="h-20 flex items-center px-6 bg-[#1E293B] border-b border-[#334155]">
        <a href="{{ Auth::user()->tieneRol('Tecnico') ? route('consumos.index') : (Auth::user()->tieneRol('Almacenista') ? route('productos.index') : route('dashboard')) }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-[#2563EB] flex items-center justify-center text-white font-bold shadow-sm transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <span class="text-base font-bold text-white tracking-tight">Inventory</span>
                <span class="block text-[11px] text-[#94A3B8] font-medium tracking-wide">Control de Inventario</span>
            </div>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
        @if(Auth::user()->tieneRol('Almacenista'))
            <!-- Pestañas para Almacenista: 1. Inventario, 2. Salida de Equipos, 3. Categorías, 4. Almacén, 5. Reportes -->
            <!-- 1. Inventario -->
            <a href="{{ route('productos.index') }}"
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('productos.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('productos.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Inventario</span>
            </a>

            <!-- 2. Salida de Equipos -->
            <a href="{{ route('consumos.index') }}"
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('consumos.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('consumos.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Salida de Equipos</span>
            </a>

            <!-- 3. Categorías -->
            <a href="{{ route('categorias.index') }}"
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('categorias.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('categorias.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>Categorías</span>
            </a>

            <!-- 4. Almacén -->
            <a href="{{ route('almacenes.index') }}"
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('almacenes.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('almacenes.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Almacén</span>
            </a>

            <!-- 5. Reportes -->
            <a href="{{ route('reportes.index') }}"
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('reportes.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('reportes.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Reportes</span>
            </a>

        @elseif(Auth::user()->tieneRol('Tecnico'))
            <!-- Pestaña para Técnico: Salida de Equipos -->
            <a href="{{ route('consumos.index') }}"
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('consumos.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('consumos.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Salida de Equipos</span>
            </a>

        @else
            <!-- Pestañas para Administrador -->
            @can('gestionar-personal')
                <a href="{{ route('usuarios.index') }}"
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('usuarios.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('usuarios.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Gestión de Personal</span>
                </a>
            @endcan

            @can('gestionar-almacen')
                <a href="{{ route('almacenes.index') }}"
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('almacenes.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('almacenes.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Gestión de Almacén</span>
                </a>
            @endcan

            @can('generar-reportes')
                <a href="{{ route('reportes.index') }}"
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('reportes.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('reportes.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Gestión de Reportes</span>
                </a>
            @endcan

            @can('gestionar-inventario')
                <!-- Inventario -->
                <a href="{{ route('productos.index') }}"
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('productos.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('productos.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Gestión de Inventario</span>
                </a>

                <!-- Salida de Equipos (debajo de Inventario) -->
                <a href="{{ route('consumos.index') }}"
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('consumos.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('consumos.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Salida de Equipos</span>
                </a>

                <!-- Categorías -->
                <a href="{{ route('categorias.index') }}"
                   class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold tracking-wide transition-all {{ request()->routeIs('categorias.*') ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#CBD5E1] hover:text-white hover:bg-[#334155]' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('categorias.*') ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Categorías</span>
                </a>
            @endcan
        @endif
    </nav>

    <!-- Footer: Cerrar Sesión -->
    <div class="p-4 border-t border-[#334155] bg-[#1E293B]">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-[#94A3B8] hover:bg-[#334155] hover:text-white transition-colors">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>
