<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-6 py-3 bg-white/10 border border-white/10 rounded-2xl font-black text-sm text-white transition hover:bg-white/15 focus:outline-none disabled:opacity-25']) }}>
    {{ $slot }}
</button>
