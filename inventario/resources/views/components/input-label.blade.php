@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-bold uppercase tracking-wider text-[#64748B] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
