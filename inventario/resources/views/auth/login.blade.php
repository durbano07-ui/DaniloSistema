<x-guest-layout>
    <div class="min-h-screen w-full flex flex-col justify-between sm:justify-center items-center bg-[#F8FAFC] relative overflow-x-hidden">
        
        <!-- SECCIÓN SUPERIOR: Identidad / Bienvenida (Hero en móvil, centrado arriba en desktop) -->
        <div class="flex-1 flex flex-col justify-center items-center text-center px-6 pt-10 pb-6 sm:pb-8 w-full max-w-md">
            <!-- Icono de la Marca -->
            <div class="w-16 h-16 rounded-2xl bg-[#2563EB] flex items-center justify-center shadow-xl shadow-black/10 mb-4 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            
            <span class="text-[11px] font-black uppercase tracking-widest text-[#64748B] mb-1">Inventory</span>
            <h1 class="text-2xl sm:text-3xl font-semibold text-[#1E293B] tracking-tight">Iniciar sesión</h1>
        </div>

        <!-- SECCIÓN INFERIOR: Tarjeta de Credenciales y Botones (Bottom-sheet en móvil, tarjeta flotante en desktop) -->
        <div class="w-full sm:max-w-md bg-white border-t sm:border border-[#E2E8F0] shadow-[0_-12px_40px_-15px_rgba(0,0,0,0.07)] sm:shadow-[0_12px_35px_-5px_rgba(0,0,0,0.04)] rounded-t-[36px] sm:rounded-2xl p-6 sm:p-8 sm:mb-8"
             x-data="{
                 showPassword: false
             }">

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Campo 1: Número de Cédula -->
                <div>
                    <label for="numero_cedula" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Número de Cédula
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                            <!-- Icono de Identificación / Usuario -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="numero_cedula"
                               class="w-full pl-11 pr-4 py-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-[#2563EB]/10 font-mono text-sm transition-all"
                               type="text"
                               name="numero_cedula"
                               value="{{ old('numero_cedula') }}"
                               placeholder="Ej. 0500000001"
                               required
                               autofocus
                               autocomplete="username" />
                    </div>
                    <x-input-error :messages="$errors->get('numero_cedula')" class="mt-1.5 text-xs text-rose-600" />
                </div>

                <!-- Campo 2: Contraseña con Visor (Ojo Mostrar/Ocultar) -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Contraseña
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                            <!-- Icono Candado -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password"
                               :type="showPassword ? 'text' : 'password'"
                               name="password"
                               placeholder="••••••••"
                               required
                               autocomplete="current-password"
                               class="w-full pl-11 pr-12 py-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-[#2563EB]/10 text-sm transition-all" />

                        <!-- Visor de Contraseña Interactivo -->
                        <button type="button"
                                @click="showPassword = !showPassword"
                                :title="showPassword ? 'Ocultar contraseña' : 'Ver contraseña'"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#94A3B8] hover:text-[#2563EB] transition-colors focus:outline-none cursor-pointer">
                            <!-- Icono Ojo (Mostrar Contraseña) -->
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Icono Ojo Tachado (Ocultar Contraseña) -->
                            <svg x-show="showPassword" x-cloak class="w-5 h-5 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-600" />
                </div>

                <!-- Recordarme -->
                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                        <input id="remember_me"
                               type="checkbox"
                               name="remember"
                               class="w-4 h-4 rounded-md bg-[#F8FAFC] border-[#E2E8F0] text-[#2563EB] focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                        <span class="ms-2 text-xs text-[#64748B] font-medium">Recordarme en este equipo</span>
                    </label>
                </div>

                <!-- Botón Principal: Ingresar al Sistema -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.99] text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-4 focus:ring-[#2563EB]/20 flex items-center justify-center gap-2 cursor-pointer">
                        <span>Ingresar</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
