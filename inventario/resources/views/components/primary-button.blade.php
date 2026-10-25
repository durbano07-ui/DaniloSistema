<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-600 transition-all duration-150']) }}>
    {{ $slot }}
</button>
