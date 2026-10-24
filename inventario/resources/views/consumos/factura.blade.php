<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('consumos.index') }}"
               class="p-2 rounded-xl text-[#64748B] hover:text-[#1E293B] hover:bg-white border border-transparent hover:border-[#E2E8F0] transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-[#0F172A] tracking-tight">Salida con Factura</h1>
        </div>
    </x-slot>

    @php
        $itemsPrevios = collect(old('items', []))->values();
    @endphp

    <form method="POST"
          action="{{ route('consumos.factura.store') }}"
          class="max-w-5xl mx-auto space-y-6"
          x-data="{
              productosPrevios: @js($productosPrevios),
              resultados: [],
              buscando: false,
              iva: {{ (float) $formato['iva'] }},
              busqueda: '',
              listaAbierta: false,
              items: @js($itemsPrevios->map(fn ($i) => [
                  'numero_serie' => $i['numero_serie'] ?? '',
                  'cantidad' => (int) ($i['cantidad'] ?? 1),
                  'precio_unitario' => $i['precio_unitario'] ?? '',
              ])),
              error: '',
              init() {
                  // Completa los datos de los equipos restaurados tras un error de validación
                  this.items = this.items
                      .map(i => ({ ...this.productosPrevios.find(p => p.numero_serie === i.numero_serie), ...i }))
                      .filter(i => i.nombre);
              },
              async consultar(q) {
                  const params = new URLSearchParams({ q });
                  this.items.forEach(i => params.append('excluir[]', i.numero_serie));
                  const respuesta = await fetch(`{{ route('consumos.factura.buscar') }}?${params}`, {
                      headers: { 'Accept': 'application/json' }
                  });
                  return respuesta.ok ? await respuesta.json() : [];
              },
              async buscar() {
                  const q = this.busqueda.trim();
                  this.error = '';
                  if (!q) { this.resultados = []; this.listaAbierta = false; return; }
                  this.buscando = true;
                  const resultados = await this.consultar(q);
                  // Ignora respuestas de búsquedas anteriores si el texto ya cambió
                  if (q !== this.busqueda.trim()) return;
                  this.resultados = resultados;
                  this.listaAbierta = true;
                  this.buscando = false;
              },
              agregar(p) {
                  this.items.push({ ...p, cantidad: 1, precio_unitario: '' });
                  this.busqueda = '';
                  this.resultados = [];
                  this.listaAbierta = false;
                  this.error = '';
                  this.$nextTick(() => this.$root.querySelector(`[data-precio='${CSS.escape(p.numero_serie)}']`)?.focus());
              },
              async agregarPorSerie() {
                  const serie = this.busqueda.trim();
                  if (!serie) return;
                  if (this.items.some(i => i.numero_serie.toLowerCase() === serie.toLowerCase())) {
                      this.error = `El equipo ${serie} ya está en el acta.`;
                      return;
                  }
                  const resultados = await this.consultar(serie);
                  const exacto = resultados.find(p => p.numero_serie.toLowerCase() === serie.toLowerCase());
                  if (exacto) { this.agregar(exacto); return; }
                  if (resultados.length === 1) { this.agregar(resultados[0]); return; }
                  this.resultados = resultados;
                  this.listaAbierta = resultados.length > 0;
                  this.error = resultados.length ? 'Hay varias coincidencias, elige el equipo de la lista.' : `No hay un equipo disponible que coincida con «${serie}».`;
              },
              quitar(index) { this.items.splice(index, 1); },
              totalLinea(i) { return Math.round((Number(i.cantidad) || 0) * (Number(i.precio_unitario) || 0) * 100) / 100; },
              get subtotal() { return Math.round(this.items.reduce((s, i) => s + this.totalLinea(i), 0) * 100) / 100; },
              get montoIva() { return Math.round(this.subtotal * this.iva * 100) / 100; },
              get total() { return this.subtotal + this.montoIva; },
              dinero(v) { return Number(v).toFixed(2); }
          }">
        @csrf

        <!-- Datos del Acta -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="fecha" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">Fecha <span class="text-rose-500">*</span></label>
                    <input type="date" id="fecha" name="fecha" required
                           value="{{ old('fecha', now()->toDateString()) }}"
                           class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2.5 text-sm text-[#1E293B] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 font-mono" />
                    @error('fecha')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">N.º de Factura</span>
                    <div class="px-3.5 py-2.5 rounded-xl bg-[#F1F5F9] border border-[#E2E8F0] text-sm font-semibold font-mono text-[#1E293B]" title="Se asigna automáticamente al registrar">{{ $siguienteFactura }}</div>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">Destino</span>
                    <div class="px-3.5 py-2.5 rounded-xl bg-[#F1F5F9] border border-[#E2E8F0] text-sm font-semibold text-[#1E293B]">{{ $formato['destino'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <span class="block font-bold uppercase tracking-wider text-[#64748B] text-[10px] mb-1">Entrega</span>
                    <span class="block font-semibold text-[#1E293B]">{{ $formato['entrega_nombre'] }}</span>
                    <span class="block text-[#64748B]">{{ $formato['entrega_cargo'] }} &middot; C.I. {{ $formato['entrega_cedula'] }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                    <span class="block font-bold uppercase tracking-wider text-[#64748B] text-[10px] mb-1">Recibe</span>
                    <span class="block font-semibold text-[#1E293B]">{{ $formato['recibe_nombre'] }}</span>
                    <span class="block text-[#64748B]">{{ $formato['recibe_cargo'] }} &middot; C.I. {{ $formato['recibe_cedula'] }}</span>
                </div>
            </div>
        </div>

        <!-- Equipos -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
            <div class="p-6 pb-4 border-b border-[#E2E8F0]">
                <label for="busqueda" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">Agregar equipos del inventario</label>
                <div class="relative" @click.outside="listaAbierta = false">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="busqueda" autocomplete="off"
                           x-model="busqueda"
                           @focus="listaAbierta = resultados.length > 0"
                           @input.debounce.250ms="buscar()"
                           @keydown.enter.prevent="agregarPorSerie()"
                           placeholder="Busca por nombre o serie, o usa el lector de código de barras"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-sm text-[#1E293B] placeholder-[#94A3B8] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />

                    <div x-show="listaAbierta && !resultados.length && busqueda.trim()"
                         x-cloak
                         class="absolute left-0 right-0 mt-2 rounded-xl bg-white border border-[#E2E8F0] shadow-xl px-4 py-3 z-30 text-sm text-[#94A3B8]">
                        Sin coincidencias en el inventario disponible
                    </div>

                    <div x-show="listaAbierta && resultados.length"
                         x-cloak
                         class="absolute left-0 right-0 mt-2 rounded-xl bg-white border border-[#E2E8F0] shadow-xl p-1.5 z-30 max-h-72 overflow-y-auto">
                        <template x-for="p in resultados" :key="p.numero_serie">
                            <button type="button"
                                    @click="agregar(p)"
                                    class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-left hover:bg-[#F8FAFC] transition-colors">
                                <span>
                                    <span class="block text-sm font-semibold text-[#1E293B]" x-text="p.nombre"></span>
                                    <span class="block text-[11px] font-mono text-[#94A3B8]" x-text="p.numero_serie"></span>
                                </span>
                                <span class="text-[11px] text-[#64748B] whitespace-nowrap" x-text="(p.categoria ?? '') + (p.tipo === 'lote' ? ' · ' + p.disponible + (p.unidad === 'metros' ? ' m' : '') + ' disp.' : '')"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <p x-show="error" x-cloak x-text="error" class="mt-2 text-xs text-rose-600 font-medium"></p>
                @error('items')<p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                @if ($errors->has('items.*'))
                    <p class="mt-2 text-xs text-rose-600 font-medium">{{ collect($errors->get('items.*'))->flatten()->first() }}</p>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F8FAFC] text-[10px] font-bold uppercase tracking-wider text-[#64748B]">
                        <tr>
                            <th class="py-3 px-6">Descripción</th>
                            <th class="py-3 px-4 w-28">Cantidad</th>
                            <th class="py-3 px-4 w-36">Precio U.</th>
                            <th class="py-3 px-4 w-32 text-right">Precio Total</th>
                            <th class="py-3 px-6 w-12"><span class="sr-only">Quitar</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        <template x-for="(item, index) in items" :key="item.numero_serie">
                            <tr>
                                <td class="py-3 px-6">
                                    <input type="hidden" :name="`items[${index}][numero_serie]`" :value="item.numero_serie">
                                    <span class="block font-semibold text-[#1E293B]" x-text="item.nombre"></span>
                                    <span class="block text-[11px] font-mono text-[#94A3B8]" x-text="'S/N: ' + item.numero_serie"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <template x-if="item.tipo === 'lote'">
                                        <input type="number" min="1" :max="item.disponible" required
                                               :name="`items[${index}][cantidad]`"
                                               x-model.number="item.cantidad"
                                               @change="if (item.cantidad < 1) item.cantidad = 1; if (item.cantidad > item.disponible) item.cantidad = item.disponible"
                                               class="w-20 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] px-2.5 py-1.5 text-sm text-right font-mono focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />
                                    </template>
                                    <template x-if="item.unidad === 'metros'">
                                        <span class="text-[11px] font-semibold text-[#64748B] pl-1">m</span>
                                    </template>
                                    <template x-if="item.tipo !== 'lote'">
                                        <span>
                                            <input type="hidden" :name="`items[${index}][cantidad]`" value="1">
                                            <span class="font-mono text-[#1E293B] pl-2.5">1</span>
                                        </span>
                                    </template>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-[#94A3B8] text-xs">$</span>
                                        <input type="number" min="0" step="0.01" required
                                               :name="`items[${index}][precio_unitario]`"
                                               :data-precio="item.numero_serie"
                                               x-model="item.precio_unitario"
                                               placeholder="0.00"
                                               class="w-28 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] pl-6 pr-2.5 py-1.5 text-sm text-right font-mono focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20" />
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-semibold text-[#1E293B]" x-text="dinero(totalLinea(item))"></td>
                                <td class="py-3 px-6 text-right">
                                    <button type="button" @click="quitar(index)"
                                            class="p-1.5 rounded-lg text-[#94A3B8] hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Quitar del acta">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!items.length">
                            <td colspan="5" class="py-10 text-center text-sm text-[#94A3B8]">Aún no hay equipos en el acta</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totales -->
            <div class="flex justify-end p-6 border-t border-[#E2E8F0]">
                <dl class="w-64 space-y-1.5 text-sm">
                    <div class="flex justify-between text-[#64748B]">
                        <dt>Subtotal</dt>
                        <dd class="font-mono text-[#1E293B]" x-text="dinero(subtotal)"></dd>
                    </div>
                    <div class="flex justify-between text-[#64748B]">
                        <dt>IVA {{ rtrim(rtrim(number_format($formato['iva'] * 100, 2), '0'), '.') }}%</dt>
                        <dd class="font-mono text-[#1E293B]" x-text="dinero(montoIva)"></dd>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-[#E2E8F0] font-semibold text-[#0F172A]">
                        <dt>Total</dt>
                        <dd class="font-mono" x-text="dinero(total)"></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Nota y Confirmación -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-6 space-y-5">
            <div>
                <label for="nota" class="block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5">Nota</label>
                <textarea id="nota" name="nota" rows="2" maxlength="500"
                          class="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-2.5 text-sm text-[#1E293B] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20">{{ old('nota') }}</textarea>
                @error('nota')<p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('consumos.index') }}"
                   class="px-4 py-2.5 rounded-xl border border-[#E2E8F0] text-[#64748B] hover:text-[#1E293B] hover:bg-[#F8FAFC] text-xs font-semibold transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        :disabled="!items.length"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span>Registrar salida y generar acta</span>
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
