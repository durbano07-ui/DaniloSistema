{{-- Enlace del menú lateral: resalta la sección activa según el patrón de ruta --}}
@props(['href', 'active' => false, 'icon'])

<a href="{{ $href }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'group relative flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-colors',
       'bg-brand-50 text-brand-700' => $active,
       'text-ink-600 hover:bg-ink-100/70 hover:text-ink-900' => ! $active,
   ]) }}>
    @if ($active)
        <span class="absolute -left-3 top-1/2 -translate-y-1/2 h-5 w-[3px] rounded-r-full bg-brand-600" aria-hidden="true"></span>
    @endif
    <svg @class([
            'w-[18px] h-[18px] flex-shrink-0 transition-colors',
            'text-brand-600' => $active,
            'text-ink-400 group-hover:text-ink-600' => ! $active,
         ])
         fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
    </svg>
    <span class="truncate">{{ $slot }}</span>
</a>
