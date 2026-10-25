<x-guest-layout>
    <div class="min-h-screen w-full grid lg:grid-cols-[1.05fr_1fr] bg-white">

        <!-- Panel de marca (solo escritorio) -->
        <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-brand-950 p-12 text-white">
            <!-- Fondo: degradado y cuadrícula sutil -->
            <div class="absolute inset-0 bg-[radial-gradient(120%_80%_at_0%_0%,theme(colors.brand.700/55%),transparent_60%),radial-gradient(90%_70%_at_100%_100%,theme(colors.brand.500/30%),transparent_55%)]" aria-hidden="true"></div>
            <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:44px_44px] [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_75%)]" aria-hidden="true"></div>

            <div class="relative flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 ring-1 ring-white/20 backdrop-blur flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Inventory') }}</span>
            </div>

            <div class="relative max-w-md">
                <h2 class="text-4xl font-semibold leading-[1.1] tracking-tightest">
                    Control total de tu inventario, en un solo lugar.
                </h2>
                <p class="mt-4 text-[15px] leading-relaxed text-brand-100/80">
                    Registra equipos y lotes, despacha a los técnicos con el escáner y genera actas y reportes listos para firmar.
                </p>

                <ul class="mt-10 space-y-4 text-sm text-brand-50/90">
                    @foreach ([
                        'Entradas por serie, lote o metros de cable',
                        'Salidas con código de barras y acta de entrega',
                        'Reportes de existencias en PDF y Excel',
                    ] as $beneficio)
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-white/10 ring-1 ring-white/15 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            {{ $beneficio }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-brand-200/60">&copy; {{ now()->year }} {{ config('app.name', 'Inventory') }} · Control de Inventario</p>
        </aside>

        <!-- Formulario -->
        <main class="flex items-center justify-center px-6 py-12 sm:px-12 bg-ink-50 lg:bg-white">
            <div class="w-full max-w-sm" x-data="{ showPassword: false }">
                <!-- Marca en móvil -->
                <div class="lg:hidden flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-800 text-white flex items-center justify-center shadow-brand-glow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-ink-950 tracking-tight">{{ config('app.name', 'Inventory') }}</span>
                </div>

                <h1 class="text-[28px] font-semibold text-ink-950 tracking-tightest">Bienvenido de nuevo</h1>
                <p class="mt-2 text-sm text-ink-500">Ingresa con tu número de cédula para continuar.</p>

                <x-auth-session-status class="mt-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    <!-- Número de cédula -->
                    <div>
                        <label for="numero_cedula" class="block text-[13px] font-medium text-ink-700 mb-1.5">Número de cédula</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-ink-400">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="numero_cedula"
                                   class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-white border border-ink-200 text-ink-900 placeholder-ink-400 shadow-xs focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 font-mono text-sm transition"
                                   type="text"
                                   name="numero_cedula"
                                   value="{{ old('numero_cedula') }}"
                                   placeholder="0500000001"
                                   inputmode="numeric"
                                   required
                                   autofocus
                                   autocomplete="username" />
                        </div>
                        <x-input-error :messages="$errors->get('numero_cedula')" class="mt-1.5 text-xs text-rose-600" />
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-[13px] font-medium text-ink-700 mb-1.5">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-ink-400">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   type="password"
                                   name="password"
                                   placeholder="••••••••"
                                   required
                                   autocomplete="current-password"
                                   class="w-full pl-11 pr-12 py-2.5 rounded-xl bg-white border border-ink-200 text-ink-900 placeholder-ink-400 shadow-xs focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 text-sm transition" />
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    class="absolute inset-y-0 right-0 px-3.5 flex items-center text-ink-400 hover:text-ink-700 transition-colors">
                                <svg x-show="!showPassword" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-[18px] h-[18px] text-brand-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-600" />
                    </div>

                    <label for="remember_me" class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input id="remember_me"
                               type="checkbox"
                               name="remember"
                               class="w-4 h-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500/30">
                        <span class="text-[13px] text-ink-600">Recordarme en este equipo</span>
                    </label>

                    <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 active:translate-y-px text-white text-sm font-semibold transition focus:outline-none focus:ring-4 focus:ring-brand-500/25 flex items-center justify-center gap-2">
                        Ingresar
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <p class="mt-10 text-xs text-ink-400">¿Olvidaste tu contraseña? Solicita al administrador o almacenista que la restablezca.</p>
            </div>
        </main>
    </div>
</x-guest-layout>
