{{-- Campos compartidos por los formularios de crear y editar proveedor --}}
@php
    $campos = [
        ['nombre', 'Nombre o Razón Social', 'text', 'Ej. Distribuidora Fibra Andina S.A.', true, 255, $proveedor?->nombre],
        ['correo', 'Correo Electrónico', 'email', 'Ej. ventas@proveedor.com', false, 255, $proveedor?->emails->first()?->correo],
        ['telefono', 'Teléfono', 'tel', 'Ej. 0991234567', false, 20, $proveedor?->telefonos->first()?->numero],
    ];
@endphp

@foreach ($campos as [$campo, $etiqueta, $tipo, $ejemplo, $requerido, $maximo, $actual])
    <div>
        <label for="{{ $campo }}" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
            {{ $etiqueta }} @if ($requerido)<span class="text-rose-500">*</span>@endif
        </label>
        <input id="{{ $campo }}"
               type="{{ $tipo }}"
               name="{{ $campo }}"
               value="{{ old($campo, $actual) }}"
               placeholder="{{ $ejemplo }}"
               @required($requerido)
               maxlength="{{ $maximo }}"
               class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
        @error($campo)
            <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $message }}
            </p>
        @enderror
    </div>
@endforeach
