<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-red-950/80 border border-transparent rounded-2xl font-black text-sm text-red-200 transition hover:bg-red-900 focus:outline-none']) }}>
    {{ $slot }}
</button>
