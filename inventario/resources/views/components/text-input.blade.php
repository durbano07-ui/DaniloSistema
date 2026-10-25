@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-ink-50 border-ink-200 text-ink-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 rounded-xl shadow-xs text-sm transition-all']) }}>
