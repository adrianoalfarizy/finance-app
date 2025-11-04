<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white/20 border border-white/30 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-0 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
