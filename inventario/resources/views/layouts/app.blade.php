<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inventory') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400..700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-ink-50 text-ink-900">
        <div x-data="{ sidebarOpen: false, changePasswordModalOpen: {{ ($errors?->hasBag('updatePassword') && $errors->getBag('updatePassword')->any()) ? 'true' : 'false' }} }" class="min-h-screen flex bg-ink-50">
            <!-- Mobile Sidebar Backdrop -->
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 z-40 bg-ink-950/40 backdrop-blur-sm lg:hidden"
                 style="display: none;"></div>

            <!-- Mobile Slide-over Sidebar -->
            <div x-show="sidebarOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 w-64 lg:hidden"
                 style="display: none;">
                @include('layouts.sidebar')
            </div>

            <!-- Desktop Sticky Sidebar -->
            <div class="hidden lg:flex lg:flex-shrink-0">
                <div class="sticky top-0 h-screen">
                    @include('layouts.sidebar')
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 bg-ink-50">
                <!-- Top Header Bar -->
                <header class="h-16 flex items-center justify-between gap-4 px-4 sm:px-8 lg:px-10 bg-white/85 border-b border-ink-200/80 backdrop-blur-xl supports-[backdrop-filter]:bg-white/70 sticky top-0 z-30">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Hamburger Button -->
                        <button @click="sidebarOpen = !sidebarOpen"
                                type="button"
                                class="lg:hidden p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-ink-100 focus:outline-none focus:ring-2 focus:ring-brand-600">
                            <span class="sr-only">Abrir menú lateral</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        @isset($header)
                            <div>{{ $header }}</div>
                        @endisset
                    </div>

                    <!-- Right user menu button & dropdown -->
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen"
                                @click.outside="userMenuOpen = false"
                                type="button"
                                class="inline-flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-full bg-white hover:bg-ink-50 border border-ink-200 hover:border-ink-300 text-ink-900 transition-all text-[13px] font-medium shadow-xs focus:outline-none focus:ring-2 focus:ring-brand-600/30">
                            <span class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-brand-800 text-white flex items-center justify-center text-[11px] font-semibold">{{ mb_strtoupper(mb_substr(Auth::user()->nombre ?? 'U', 0, 1)) }}</span>
                            <span class="hidden sm:inline max-w-xs truncate">{{ Auth::user()->nombre }}</span>
                            <svg class="w-3.5 h-3.5 text-ink-500 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 mt-2.5 w-64 rounded-2xl bg-white border border-ink-200 shadow-elevated ring-1 ring-ink-900/5 py-2 z-50 divide-y divide-ink-100"
                             style="display: none;">

                            <!-- Info de Usuario -->
                            <div class="px-5 py-3">
                                <p class="text-xs font-bold text-ink-900 truncate">{{ Auth::user()->nombre }}</p>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-brand-50 text-brand-600 border border-brand-200">
                                        {{ Auth::user()->rol?->nombre ?? 'Usuario' }}
                                    </span>
                                    <span class="text-[11px] text-ink-500 font-mono">{{ Auth::user()->numero_cedula }}</span>
                                </div>
                            </div>

                            <!-- Opciones según el Rol -->
                            <div class="py-1.5">
                                @if(Auth::user()->tieneRol('Tecnico') || Auth::user()->tieneRol('Almacenista'))
                                    <!-- Opción única para Técnico y Almacenista: Cambiar Contraseña -->
                                    <button type="button"
                                            @click="userMenuOpen = false; changePasswordModalOpen = true"
                                            class="w-full flex items-center gap-3 px-5 py-2.5 text-xs font-semibold text-ink-600 hover:text-brand-600 hover:bg-ink-50 transition-colors text-left group">
                                        <svg class="w-4 h-4 text-brand-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        <span>Cambiar Contraseña</span>
                                    </button>
                                @else
                                    <!-- Opciones para Administrador -->
                                    @can('gestionar-personal')
                                        <a href="{{ route('usuarios.index') }}"
                                           class="flex items-center gap-3 px-5 py-2.5 text-xs font-semibold text-ink-600 hover:text-ink-900 hover:bg-ink-50 transition-colors group">
                                            <svg class="w-4 h-4 text-ink-500 group-hover:text-ink-900 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            <span>Gestión de Personal</span>
                                        </a>
                                    @endcan

                                    <button type="button"
                                            @click="userMenuOpen = false; changePasswordModalOpen = true"
                                            class="w-full flex items-center gap-3 px-5 py-2.5 text-xs font-semibold text-ink-600 hover:text-brand-600 hover:bg-ink-50 transition-colors text-left group">
                                        <svg class="w-4 h-4 text-brand-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        <span>Cambiar Contraseña</span>
                                    </button>
                                @endif
                            </div>

                            <!-- Cerrar Sesión -->
                            <div class="py-1.5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-3 px-5 py-2.5 text-xs font-semibold text-brand-600 hover:text-brand-700 hover:bg-ink-50 transition-colors text-left group">
                                        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Cerrar Sesión</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content & Flash Alerts -->
                <main class="flex-1 px-4 py-6 sm:p-8 lg:px-10 lg:py-8 overflow-y-auto bg-ink-50">
                    <div class="mx-auto w-full max-w-[1440px]">
                    <!-- Session Feedback Alerts -->
                    @if (session('status') || session('success'))
                        <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50/80 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs" role="status">
                            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium">
                                @if (session('status') === 'password-updated')
                                    Tu contraseña ha sido actualizada exitosamente.
                                @else
                                    {{ session('status') ?? session('success') }}
                                @endif
                            </span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 px-4 py-3 rounded-xl bg-rose-50/80 border border-rose-200 text-rose-900 flex items-center gap-3 shadow-xs" role="alert">
                            <svg class="w-5 h-5 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{ $slot }}
                    </div>
                </main>
            </div>

            <!-- Modal para Cambiar Contraseña -->
            <div x-show="changePasswordModalOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-950/40 backdrop-blur-sm"
                 style="display: none;"
                 @keydown.escape.window="changePasswordModalOpen = false">

                <div @click.outside="changePasswordModalOpen = false"
                     class="w-full max-w-md rounded-2xl bg-white border border-ink-200 shadow-elevated ring-1 ring-ink-900/5 overflow-hidden p-6 sm:p-8 text-ink-900">
                    
                    <div class="flex items-center justify-between pb-5 border-b border-ink-100 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 border border-brand-200 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-ink-900">Cambiar Contraseña</h3>
                                <p class="text-xs text-ink-500">Actualiza tu clave de acceso al sistema</p>
                            </div>
                        </div>
                        <button type="button" @click="changePasswordModalOpen = false" class="text-ink-500 hover:text-ink-900 p-1.5 rounded-xl hover:bg-ink-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Contraseña Actual -->
                        <div>
                            <label for="modal_current_password" class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1.5">
                                Contraseña Actual <span class="text-rose-500">*</span>
                            </label>
                            <input id="modal_current_password"
                                   type="password"
                                   name="current_password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full rounded-xl bg-ink-50 border border-ink-200 px-3.5 py-2.5 text-sm text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition-all font-mono" />
                            @if ($errors?->hasBag('updatePassword') && $errors->getBag('updatePassword')->has('current_password'))
                                <p class="mt-1.5 text-xs text-rose-600">{{ $errors->getBag('updatePassword')->first('current_password') }}</p>
                            @endif
                        </div>

                        <!-- Nueva Contraseña -->
                        <div>
                            <label for="modal_password" class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1.5">
                                Nueva Contraseña <span class="text-rose-500">*</span>
                            </label>
                            <input id="modal_password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••"
                                   class="w-full rounded-xl bg-ink-50 border border-ink-200 px-3.5 py-2.5 text-sm text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition-all font-mono" />
                            @if ($errors?->hasBag('updatePassword') && $errors->getBag('updatePassword')->has('password'))
                                <p class="mt-1.5 text-xs text-rose-600">{{ $errors->getBag('updatePassword')->first('password') }}</p>
                            @endif
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="modal_password_confirmation" class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1.5">
                                Confirmar Nueva Contraseña <span class="text-rose-500">*</span>
                            </label>
                            <input id="modal_password_confirmation"
                                   type="password"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••"
                                   class="w-full rounded-xl bg-ink-50 border border-ink-200 px-3.5 py-2.5 text-sm text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition-all font-mono" />
                            @if ($errors?->hasBag('updatePassword') && $errors->getBag('updatePassword')->has('password_confirmation'))
                                <p class="mt-1.5 text-xs text-rose-600">{{ $errors->getBag('updatePassword')->first('password_confirmation') }}</p>
                            @endif
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-5 border-t border-ink-100 mt-6">
                            <button type="button"
                                    @click="changePasswordModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-ink-500 hover:text-ink-900 hover:bg-ink-100 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-600">
                                Guardar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
