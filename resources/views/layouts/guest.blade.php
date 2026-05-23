<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Movie Night') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen bg-surface-black text-white">
    <header class="sticky top-0 z-30 flex h-[72px] items-center justify-between border-b border-white/10 bg-surface-black/90 px-5 backdrop-blur-xl sm:px-8 lg:px-12">
        <a href="/" class="text-lg font-black tracking-tight text-red-500 sm:text-xl">MOVIE NIGHT</a>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-sm font-semibold text-zinc-300 transition hover:text-white">Sign In</a>
            <a href="{{ route('register') }}" class="rounded-full bg-red-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-950/40 transition hover:-translate-y-0.5 hover:bg-red-400">Get Started</a>
        </div>
    </header>
    <main>
        {{ $slot }}
    </main>
</body>
</html>
