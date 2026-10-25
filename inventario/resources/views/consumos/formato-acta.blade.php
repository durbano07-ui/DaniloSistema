<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('consumos.index') }}"
               class="p-2 rounded-xl text-ink-500 hover:text-ink-900 hover:bg-white border border-transparent hover:border-ink-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Formato del Acta de Entrega</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('consumos.formato-acta.update') }}"
          class="max-w-3xl mx-auto bg-white rounded-2xl border border-ink-200 shadow-card p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        @php
            $grupos = [
                'General' => ['empresa', 'ciudad', 'destino'],
                'Entrega' => ['entrega_nombre', 'entrega_cargo', 'entrega_cedula', 'entrega_ci_firma'],
                'Recibe' => ['recibe_nombre', 'recibe_cargo', 'recibe_cedula'],
            ];
        @endphp

        @foreach ($grupos as $grupo => $campos)
            <fieldset class="space-y-4">
                <legend class="text-xs font-bold uppercase tracking-wider text-ink-900 pb-2 border-b border-ink-200 w-full">{{ $grupo }}</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($campos as $campo)
                        <div>
                            <label for="{{ $campo }}" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">
                                {{ \App\Support\FormatoActa::CAMPOS[$campo] }}
                            </label>
                            <input type="text" id="{{ $campo }}" name="{{ $campo }}" required maxlength="150"
                                   value="{{ old($campo, $formato[$campo]) }}"
                                   class="w-full rounded-xl bg-ink-50 border border-ink-200 px-3.5 py-2.5 text-sm text-ink-900 focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" />
                            @error($campo)<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <fieldset class="space-y-4">
            <legend class="text-xs font-bold uppercase tracking-wider text-ink-900 pb-2 border-b border-ink-200 w-full">Impuestos</legend>
            <div class="sm:w-1/2">
                <label for="iva" class="block text-xs font-bold uppercase tracking-wider text-ink-500 mb-1.5">IVA (%)</label>
                <div class="relative">
                    <input type="number" id="iva" name="iva" required min="0" max="100" step="0.01"
                           value="{{ old('iva', rtrim(rtrim(number_format($formato['iva'] * 100, 2, '.', ''), '0'), '.')) }}"
                           class="w-full rounded-xl bg-ink-50 border border-ink-200 pl-3.5 pr-9 py-2.5 text-sm text-ink-900 font-mono focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" />
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-sm text-ink-400">%</span>
                </div>
                @error('iva')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
            </div>
        </fieldset>

        <p class="text-xs text-ink-500">Los cambios se aplican a las actas nuevas. Las actas ya emitidas conservan los datos con que se generaron.</p>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('consumos.index') }}"
               class="px-4 py-2.5 rounded-xl border border-ink-200 text-ink-500 hover:text-ink-900 hover:bg-ink-50 text-xs font-semibold transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all">
                Guardar formato
            </button>
        </div>
    </form>
</x-app-layout>
