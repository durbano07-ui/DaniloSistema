<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('reportes.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Generar Reporte Oficial</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden p-6 sm:p-8"
             x-data="{ tipo: '{{ old('tipo', 'Inventario General') }}' }">
            <div class="mb-6 pb-6 border-b border-[#F1F5F9]">
                <h2 class="text-base font-bold text-[#1E293B]">Parámetros de Emisión</h2>
                <p class="text-xs text-[#64748B] mt-1">
                    Selecciona el tipo de informe que deseas compilar. La información se consolidará en un documento formal con bloque de firmas oficial.
                </p>
            </div>

            <form method="POST" action="{{ route('reportes.store') }}" class="space-y-6">
                @csrf

                <!-- Tipo de Reporte -->
                <div>
                    <label for="tipo" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Tipo de Reporte <span class="text-rose-500">*</span>
                    </label>
                    <select id="tipo"
                            name="tipo"
                            x-model="tipo"
                            required
                            class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">
                        <option value="Inventario General">
                            Inventario General (Balance global de todos los productos y existencias)
                        </option>
                        <option value="Existencias por Bodega">
                            Existencias por Bodega (Desglose por ubicación física de almacenes)
                        </option>
                        <option value="Salidas y Despachos">
                            Salidas y Despachos Técnicos (Historial consolidado de retiros de equipos y vales)
                        </option>
                        <option value="Personal y Roles">
                            Personal y Roles (Padrón de usuarios y estados de acceso)
                        </option>
                    </select>
                    @error('tipo')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Fecha de Emisión del Documento -->
                <div>
                    <label for="fecha" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                        Fecha de Emisión <span class="text-rose-500">*</span>
                    </label>
                    <input id="fecha"
                           type="date"
                           name="fecha"
                           value="{{ old('fecha', date('Y-m-d')) }}"
                           required
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all" />
                    @error('fecha')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Parámetros de Filtro para Salidas y Despachos -->
                <div x-show="tipo === 'Salidas y Despachos'" x-cloak x-transition class="space-y-4 p-5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <div class="border-b border-[#E2E8F0] pb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#1E293B] block">Filtros para Salidas y Despachos</span>
                        <p class="text-[11px] text-[#64748B] mt-0.5">Puedes acotar las salidas por rango de fechas, técnico o bodega.</p>
                    </div>

                    <!-- Rango de Fechas -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="fecha_desde" class="block text-[11px] font-bold uppercase tracking-wider text-[#64748B] mb-1">
                                Salidas Desde
                            </label>
                            <input id="fecha_desde"
                                   type="date"
                                   name="fecha_desde"
                                   value="{{ old('fecha_desde') }}"
                                   class="w-full rounded-xl bg-white border border-[#E2E8F0] px-3 py-2 text-[#1E293B] text-xs focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all" />
                        </div>
                        <div>
                            <label for="fecha_hasta" class="block text-[11px] font-bold uppercase tracking-wider text-[#64748B] mb-1">
                                Salidas Hasta
                            </label>
                            <input id="fecha_hasta"
                                   type="date"
                                   name="fecha_hasta"
                                   value="{{ old('fecha_hasta') }}"
                                   class="w-full rounded-xl bg-white border border-[#E2E8F0] px-3 py-2 text-[#1E293B] text-xs focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all" />
                        </div>
                    </div>

                    <!-- Filtrar por Técnico -->
                    <div>
                        <label for="numero_cedula_tecnico" class="block text-[11px] font-bold uppercase tracking-wider text-[#64748B] mb-1">
                            Técnico Responsable
                        </label>
                        <select id="numero_cedula_tecnico"
                                name="numero_cedula_tecnico"
                                class="w-full rounded-xl bg-white border border-[#E2E8F0] px-3 py-2 text-[#1E293B] text-xs focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all">
                            <option value="">Todos los Técnicos</option>
                            @foreach ($tecnicos as $tec)
                                <option value="{{ $tec->numero_cedula }}" {{ old('numero_cedula_tecnico') == $tec->numero_cedula ? 'selected' : '' }}>
                                    {{ $tec->nombre }} (CI: {{ $tec->numero_cedula }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtrar por Bodega de Salida -->
                    <div>
                        <label for="id_almacen_salidas" class="block text-[11px] font-bold uppercase tracking-wider text-[#64748B] mb-1">
                            Bodega de Despacho
                        </label>
                        <select id="id_almacen_salidas"
                                name="id_almacen"
                                class="w-full rounded-xl bg-white border border-[#E2E8F0] px-3 py-2 text-[#1E293B] text-xs focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all">
                            <option value="">Todas las Bodegas</option>
                            @foreach ($almacenes as $alm)
                                <option value="{{ $alm->id_almacen }}" {{ old('id_almacen') == $alm->id_almacen ? 'selected' : '' }}>
                                    {{ $alm->nombre }} ({{ $alm->direccion?->ciudad?->nombre ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filtrar por Almacén Específico (Solo para Existencias por Bodega) -->
                <div x-show="tipo === 'Existencias por Bodega'" x-cloak x-transition class="space-y-2 p-5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <label for="id_almacen" class="block text-xs font-bold uppercase tracking-wider text-[#64748B]">
                        Almacén a Inspeccionar
                    </label>
                    <select id="id_almacen"
                            name="id_almacen"
                            class="w-full rounded-xl bg-white border border-[#E2E8F0] px-4 py-2.5 text-[#1E293B] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-sm transition-all">
                        <option value="">Todas las Bodegas (Consolidado General)</option>
                        @foreach ($almacenes as $alm)
                            <option value="{{ $alm->id_almacen }}" {{ old('id_almacen') == $alm->id_almacen ? 'selected' : '' }}>
                                {{ $alm->nombre }} ({{ $alm->direccion?->ciudad?->nombre ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-[#64748B]">Si no seleccionas un almacén, se incluirán todas las bodegas del sistema.</p>
                    @error('id_almacen')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#F1F5F9]">
                    <a href="{{ route('reportes.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] bg-white text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-sm font-semibold transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        Generar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
