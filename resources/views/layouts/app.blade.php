<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Movie Night') }} - @yield('title', '')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    @auth
    <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-surface-black text-white">
        @include('layouts.navigation')

        @if(session('status'))
            <div class="bg-green-900/80 border-b border-green-800 transition-all duration-300" :class="$store.sidebar.expanded ? 'md:pl-60' : 'md:pl-20'">
                <div class="py-3 px-5 sm:px-8 lg:px-12">
                    <p class="text-green-400 text-sm font-medium">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-900/80 border-b border-red-800 transition-all duration-300" :class="$store.sidebar.expanded ? 'md:pl-60' : 'md:pl-20'">
                <div class="py-3 px-5 sm:px-8 lg:px-12">
                    @foreach($errors->all() as $error)
                        <p class="text-red-400 text-sm font-medium">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <main class="transition-all duration-300" :class="$store.sidebar.expanded ? 'md:pl-60' : 'md:pl-20'">
            {{ $slot }}
        </main>
    </div>
    @endauth

    @guest
    <div class="min-h-screen bg-surface-black text-white">
        @include('layouts.navigation')
        <main>
            {{ $slot }}
        </main>
    </div>
    @endguest

    <script>document.addEventListener('alpine:init',()=>{Alpine.store('sidebar',{expanded:localStorage.getItem('sidebarExpanded')!=='false',toggle(){this.expanded=!this.expanded;localStorage.setItem('sidebarExpanded',this.expanded)}})})</script>
    @livewireScripts
</body>
</html>
