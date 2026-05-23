@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-zinc-300 mb-1']) }}>
    {{ $value ?? $slot }}
</label>
