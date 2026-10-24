<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2563EB] transition-all duration-150']) }}>
    {{ $slot }}
</button>
