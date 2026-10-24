<x-guest-layout>
    <div class="min-h-screen w-full flex items-center justify-center bg-[#F8FAFC] px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-[0_12px_35px_-5px_rgba(0,0,0,0.04)] sm:p-8">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#2563EB] shadow-xl">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-[#64748B]">Inventory</span>
                <h1 class="mt-2 text-2xl font-semibold text-[#1E293B]">Crear cuenta</h1>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Nombre</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 py-3 text-sm text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-[#2563EB]/10" placeholder="Nombre completo" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs text-rose-600" />
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Correo</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 py-3 text-sm text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-[#2563EB]/10" placeholder="correo@ejemplo.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-600" />
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 py-3 text-sm text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-[#2563EB]/10" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-600" />
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 py-3 text-sm text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-[#2563EB]/10" />
                </div>

                <button type="submit" class="w-full rounded-xl bg-[#2563EB] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#1D4ED8] focus:outline-none focus:ring-4 focus:ring-[#2563EB]/20">
                    Registrarse
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-[#64748B]">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="font-semibold text-[#2563EB]">Inicia sesión</a>
            </p>
        </div>
    </div>
</x-guest-layout>
