@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-white/20 bg-white/10 text-white placeholder-white/60 focus:border-white focus:ring-white/60 rounded-md shadow-sm']) }}>
