<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-white tracking-tight">Ajustes de Cuenta</h1>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-ink-800 text-emerald-400 border border-ink-700">
                {{ Auth::user()->rol?->nombre ?? 'Usuario' }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Tarjeta de Cambiar Contraseña -->
        <div class="p-6 sm:p-8 rounded-xl bg-ink-900/90 border border-ink-800 shadow-xl backdrop-blur-sm">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        @if(Auth::user()->tieneRol('Administrador'))
            <div class="p-6 sm:p-8 rounded-xl bg-ink-900/90 border border-ink-800 shadow-xl backdrop-blur-sm">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 rounded-xl bg-ink-900/90 border border-ink-800 shadow-xl backdrop-blur-sm">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
