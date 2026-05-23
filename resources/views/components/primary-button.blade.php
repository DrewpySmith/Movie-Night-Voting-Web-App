<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-red-500 border border-transparent rounded-2xl font-black text-sm text-white shadow-lg shadow-red-950/40 transition hover:-translate-y-0.5 hover:bg-red-400 focus:outline-none']) }}>
    {{ $slot }}
</button>
