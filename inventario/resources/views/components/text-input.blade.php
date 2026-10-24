@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-[#F8FAFC] border-[#E2E8F0] text-[#1E293B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 rounded-xl shadow-xs text-sm transition-all']) }}>
