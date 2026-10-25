<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-ink-950 tracking-tight">Salida de Equipos</h1>
    </x-slot>

    <div class="space-y-6" x-data="{ modalOrden: null }" @keydown.escape.window="modalOrden = null">
        <!-- Filter Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <form method="GET" action="{{ route('consumos.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="fecha" class="text-xs font-bold uppercase tracking-wider text-ink-500">Fecha:</label>
                    <input type="date"
                           id="fecha"
                           name="fecha"
                           value="{{ $fecha }}"
                           onchange="this.form.submit()"
                           class="rounded-xl bg-white border border-ink-200 text-ink-900 text-xs py-2 px-3 focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600 font-mono shadow-xs" />
                </div>

                @if($fecha !== now()->toDateString())
                    <a href="{{ route('consumos.index') }}" class="text-xs text-brand-600 hover:underline flex items-center gap-1 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Volver a hoy</span>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-4 ml-auto">
                <span class="text-xs text-ink-500">
                    {{ $ordenes->total() }} {{ $ordenes->total() === 1 ? 'orden' : 'órdenes' }}
                </span>

                @can('editar-formato-reporte')
                    <a href="{{ route('consumos.formato-acta.edit') }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-ink-200 bg-white text-xs font-semibold text-ink-500 hover:text-ink-900 hover:border-ink-300 shadow-xs transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Formato del Acta</span>
                    </a>
                @endcan

                @can('gestionar-inventario')
                <!-- Botón Nueva Orden (+) con opciones -->
                <div class="relative" x-data="{ menuNueva: false }" @click.outside="menuNueva = false" @keydown.escape="menuNueva = false">
                    <button type="button"
                            @click="menuNueva = !menuNueva"
                            :aria-expanded="menuNueva"
                            title="Nueva Orden de Salida"
                            aria-label="Nueva Orden de Salida"
                            class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <svg class="w-5 h-5 transition-transform duration-200" :class="menuNueva ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>

                    <div x-show="menuNueva"
                         x-transition
                         x-cloak
                         class="absolute right-0 mt-2 w-56 rounded-xl bg-white border border-ink-200 shadow-xl p-1.5 z-40">
                        <a href="{{ route('consumos.create') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-ink-900 hover:bg-ink-50 transition-colors">
                            <svg class="w-4 h-4 text-ink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Salida normal</span>
                        </a>
                            <a href="{{ route('consumos.factura.create') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-ink-900 hover:bg-ink-50 transition-colors">
                                <svg class="w-4 h-4 text-ink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                                <span>Salida con factura</span>
                            </a>
                    </div>
                </div>
                @else
                    <!-- Sin acceso a salidas con factura: el botón (+) abre directamente la salida normal -->
                    <a href="{{ route('consumos.create') }}"
                       title="Nueva Orden de Salida"
                       aria-label="Nueva Orden de Salida"
                       class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-brand-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Órdenes de Salida -->
        <div class="bg-white border border-ink-200 rounded-2xl shadow-card overflow-hidden">
            @if ($ordenes->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-ink-50 border border-ink-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-ink-900">
                        {{ $fecha === now()->toDateString() ? 'No hay órdenes de salida hoy' : 'No hay órdenes de salida en esta fecha' }}
                    </h3>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-ink-200 bg-ink-50 text-[11px] font-bold uppercase tracking-wider text-ink-500">
                                <th class="py-4 px-6">Orden</th>
                                <th class="py-4 px-6">Técnico</th>
                                <th class="py-4 px-6">Hora</th>
                                <th class="py-4 px-6">Equipos</th>
                                <th class="py-4 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 text-sm">
                        @foreach ($ordenes as $orden)
                                <tr class="hover:bg-ink-50/80 transition-colors">
                                    <!-- Orden -->
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="font-mono text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200">
                                            #ORD-{{ str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                        @if ($orden->esFactura())
                                            <span class="ml-1.5 text-[10px] font-bold uppercase tracking-wider text-ink-500 bg-ink-100 px-2 py-0.5 rounded-full border border-ink-200">Factura</span>
                                        @endif
                                    </td>

                                    <!-- Técnico -->
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-ink-900 text-white flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($orden->usuario?->nombre ?? 'T', 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="block text-xs font-bold text-ink-900">
                                                    {{ $orden->usuario?->nombre ?? 'Usuario #' . $orden->numero_cedula }}
                                                </span>
                                                <span class="block text-[11px] text-ink-500 font-mono">
                                                    CI: {{ $orden->numero_cedula }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Hora -->
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-ink-500 font-mono">
                                        {{ $orden->created_at?->format('H:i') ?? '—' }}
                                    </td>

                                    <!-- Equipos -->
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-ink-900 font-semibold">
                                        {{ $orden->consumos_count }} {{ $orden->consumos_count === 1 ? 'equipo' : 'equipos' }}
                                        @if ((int) $orden->consumos_sum_cantidad !== $orden->consumos_count || $orden->consumos->contains(fn ($c) => $c->producto?->esMetros()))
                                            <span class="text-ink-500 font-normal">· {{ $orden->resumenCantidades() }}</span>
                                        @endif
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <button type="button"
                                                @click="modalOrden = {{ $orden->id_orden }}"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-ink-200 bg-white text-ink-900 hover:border-brand-600 hover:text-brand-600 text-xs font-semibold transition-all">
                                            <span>Ver detalle</span>
                                        </button>
                                    </td>
                                </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($ordenes->hasPages())
                    <div class="p-4 border-t border-ink-200 bg-ink-50">
                        {{ $ordenes->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- Modales de Detalle de Orden -->
        @foreach ($ordenes as $orden)
            <div x-show="modalOrden === {{ $orden->id_orden }}"
                 x-cloak
                 x-transition.opacity
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-ink-900/60 backdrop-blur-sm"
                 @click.self="modalOrden = null"
                 role="dialog"
                 aria-modal="true">
                <div class="w-full max-w-2xl bg-white rounded-2xl border border-ink-200 shadow-xl overflow-hidden">
                    <!-- Encabezado -->
                    <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-ink-200">
                        <div>
                            <h3 class="text-base font-semibold text-ink-950">Orden #ORD-{{ str_pad($orden->id_orden, 4, '0', STR_PAD_LEFT) }}</h3>
                            <p class="text-xs text-ink-500 mt-0.5">
                                {{ $orden->usuario?->nombre ?? 'Usuario #' . $orden->numero_cedula }}
                                &middot; {{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }} {{ $orden->created_at?->format('H:i') }}
                            </p>
                        </div>
                        <button type="button"
                                @click="modalOrden = null"
                                class="p-1.5 rounded-lg text-ink-400 hover:text-ink-900 hover:bg-ink-100 transition-colors"
                                aria-label="Cerrar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Equipos de la Orden -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-ink-50 text-[10px] font-bold uppercase tracking-wider text-ink-500 sticky top-0">
                                <tr>
                                    <th class="py-2.5 px-6">Equipo</th>
                                    <th class="py-2.5 px-4">Categoría</th>
                                    <th class="py-2.5 px-4 text-right">Cantidad</th>
                                    @can('registrar-consumo')
                                        <th class="py-2.5 px-6 text-right"><span class="sr-only">Anular</span></th>
                                    @endcan
                                </tr>
                            </thead>
                            @foreach ($orden->consumos as $c)
                                <tbody class="border-b border-ink-100 last:border-b-0"
                                       x-data="{
                                           abierto: false,
                                           notas: @js(Gate::allows('gestionar-inventario') ? $c->notas->map(fn ($n) => \App\Http\Controllers\NotaConsumoController::formato($n))->values() : []),
                                           texto: '',
                                           guardando: false,
                                           error: '',
                                           async guardar() {
                                               if (!this.texto.trim() || this.guardando) return;
                                               this.guardando = true;
                                               this.error = '';
                                               try {
                                                   const respuesta = await fetch('{{ route('consumos.notas.store', $c) }}', {
                                                       method: 'POST',
                                                       headers: {
                                                           'Content-Type': 'application/json',
                                                           'Accept': 'application/json',
                                                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                       },
                                                       body: JSON.stringify({ texto: this.texto })
                                                   });
                                                   const datos = await respuesta.json();
                                                   if (!respuesta.ok) throw new Error(datos.message || 'No se pudo guardar la nota.');
                                                   this.notas.unshift(datos);
                                                   this.texto = '';
                                               } catch (e) {
                                                   this.error = e.message;
                                               } finally {
                                                   this.guardando = false;
                                               }
                                           }
                                       }">
                                    <tr>
                                        <td class="py-3 px-6">
                                            <span class="block font-semibold text-ink-900">{{ $c->producto?->nombre ?? 'Producto no encontrado' }}</span>
                                            <span class="block font-mono text-[11px] text-ink-400 mt-0.5">{{ $c->numero_serie }}</span>
                                            @can('gestionar-inventario')
                                                <button type="button"
                                                        @click="abierto = !abierto"
                                                        :class="abierto ? 'text-brand-600' : 'text-ink-500 hover:text-brand-600'"
                                                        class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-6 8l-4-4V6a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H7z" />
                                                    </svg>
                                                    <span x-text="notas.length ? `Notas (${notas.length})` : 'Agregar nota'"></span>
                                                </button>
                                            @endcan
                                        </td>
                                        <td class="py-3 px-4 text-ink-500 whitespace-nowrap">
                                            {{ $c->producto?->categoria?->nombre ?? 'Sin categoría' }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-ink-900">
                                            {{ $c->cantidad ?? 1 }}{{ $c->producto?->esMetros() ? ' m' : '' }}
                                        </td>
                                        @can('registrar-consumo')
                                            <td class="py-3 px-6 text-right whitespace-nowrap">
                                                <form method="POST"
                                                      action="{{ route('consumos.destroy', $c) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('¿Estás seguro de anular este retiro?\n\nEl equipo ({{ $c->numero_serie }}) volverá a estar DISPONIBLE y se reintegrará al stock de inventario.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg bg-ink-50 hover:bg-rose-50 border border-ink-200 hover:border-rose-200 text-ink-500 hover:text-rose-600 transition-all"
                                                            title="Anular y devolver al inventario">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                    @can('gestionar-inventario')
                                        <!-- Notas de seguimiento del equipo -->
                                        <tr x-show="abierto" x-cloak>
                                            <td colspan="4" class="px-6 pb-4 pt-0">
                                                <div class="rounded-xl bg-ink-50 border border-ink-200 p-3 space-y-3">
                                                    <form @submit.prevent="guardar()" class="flex items-start gap-2">
                                                        <textarea x-model="texto"
                                                                  rows="2"
                                                                  maxlength="1000"
                                                                  placeholder="Escribe una nota de seguimiento…"
                                                                  @keydown.ctrl.enter.prevent="guardar()"
                                                                  class="flex-1 rounded-lg bg-white border border-ink-200 px-3 py-2 text-xs text-ink-900 placeholder-ink-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 resize-none"></textarea>
                                                        <button type="submit"
                                                                :disabled="!texto.trim() || guardando"
                                                                class="px-3.5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-text="guardando ? 'Guardando…' : 'Agregar'"></span>
                                                        </button>
                                                    </form>
                                                    <p x-show="error" x-text="error" class="text-xs text-rose-600 font-medium"></p>

                                                    <ul x-show="notas.length" class="space-y-2">
                                                        <template x-for="nota in notas" :key="nota.id">
                                                            <li class="rounded-lg bg-white border border-ink-200 px-3 py-2">
                                                                <div class="flex items-center justify-between gap-3 text-[10px] text-ink-400">
                                                                    <span class="font-semibold text-ink-500" x-text="nota.autor"></span>
                                                                    <span class="font-mono" x-text="nota.fecha"></span>
                                                                </div>
                                                                <p class="mt-1 text-xs text-ink-900 whitespace-pre-line break-words" x-text="nota.texto"></p>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                    <p x-show="!notas.length" class="text-[11px] text-ink-400">Sin notas todavía</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endcan
                                </tbody>
                            @endforeach
                        </table>
                    </div>

                    <!-- Pie -->
                    <div class="flex items-center justify-between gap-4 px-6 py-4 border-t border-ink-200 bg-ink-50">
                        <span class="text-xs text-ink-500">
                            {{ $orden->consumos_count }} {{ $orden->consumos_count === 1 ? 'equipo' : 'equipos' }}
                            &middot; {{ $orden->resumenCantidades() }}
                        </span>
                        <a href="{{ $orden->esFactura() ? route('consumos.acta', $orden) : route('consumos.orden', $orden) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ $orden->esFactura() ? 'Ver acta de entrega' : 'Ver comprobante' }}</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
