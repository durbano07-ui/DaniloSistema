<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('reportes.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Editar Formato Institucional de Reportes</h1>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-8">
            <div class="mb-6 pb-6 border-b border-[#F1F5F9]">
                <h2 class="text-base font-bold text-[#1E293B]">Parámetros del Membrete y Pie de Firma</h2>
                <p class="text-xs text-[#64748B] mt-1">
                    Personaliza los datos institucionales que se imprimirán en el encabezado oficial de todos los reportes emitidos en el sistema.
                </p>
            </div>

            <form method="POST" action="{{ route('reportes.formato.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Nombre de la Empresa / Institución -->
                <div>
                    <label for="empresa" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Nombre de la Empresa o Entidad <span class="text-rose-500">*</span>
                    </label>
                    <input id="empresa"
                           type="text"
                           name="empresa"
                           value="{{ old('empresa', $formato['empresa']) }}"
                           required
                           maxlength="100"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('empresa')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Departamento o Unidad Responsable -->
                <div>
                    <label for="departamento" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Departamento o Área Emisora <span class="text-rose-500">*</span>
                    </label>
                    <input id="departamento"
                           type="text"
                           name="departamento"
                           value="{{ old('departamento', $formato['departamento']) }}"
                           required
                           maxlength="150"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('departamento')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación Geográfica / Ciudad -->
                <div>
                    <label for="ubicacion" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Ubicación y Leyenda Geográfica <span class="text-rose-500">*</span>
                    </label>
                    <input id="ubicacion"
                           type="text"
                           name="ubicacion"
                           value="{{ old('ubicacion', $formato['ubicacion']) }}"
                           required
                           maxlength="150"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('ubicacion')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nota Legal y Cláusula de Auditoría -->
                <div>
                    <label for="nota_legal" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Cláusula Oficial al Pie del Reporte <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="nota_legal"
                              name="nota_legal"
                              rows="3"
                              required
                              maxlength="500"
                              class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">{{ old('nota_legal', $formato['nota_legal']) }}</textarea>
                    @error('nota_legal')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Previsualización del membrete -->
                <div class="rounded-xl bg-[#F8FAFC] p-5 border border-[#E2E8F0] text-[#1E293B] shadow-xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block mb-2">Vista previa aproximada del membrete impreso:</span>
                    <div class="border-b-2 border-[#1E293B] pb-3 flex justify-between items-center">
                        <div>
                            <p class="font-black text-lg uppercase tracking-tight text-[#1E293B]">{{ $formato['empresa'] }}</p>
                            <p class="text-xs font-semibold text-[#64748B] uppercase">{{ $formato['departamento'] }}</p>
                            <p class="text-[11px] text-[#94A3B8]">{{ $formato['ubicacion'] }}</p>
                        </div>
                        <div class="text-right">
                            <span class="bg-[#1E293B] text-white font-mono text-[10px] font-bold px-2 py-0.5 rounded-md">#REP-0001</span>
                            <p class="text-[11px] text-[#64748B] mt-1 font-medium">{{ date('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#F1F5F9]">
                    <a href="{{ route('reportes.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        Guardar Formato
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
