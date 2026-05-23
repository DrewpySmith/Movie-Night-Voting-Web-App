@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-white bg-netflix-accent/20 border border-netflix-accent/30 transition'
            : 'flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
