<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Movie Night') }} - {{ $title ?? 'Admin' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="font-sans antialiased">
    @include('layouts.navigation')

    <div class="transition-all duration-300" :class="$store.sidebar.expanded ? 'md:pl-60' : 'md:pl-20'">
    <div class="max-w-7xl mx-auto px-5 py-8 sm:px-8 lg:px-12">
        <div class="flex gap-8">
            <aside class="w-56 shrink-0 hidden lg:block">
                <nav class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'bg-red-500 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5' }} transition text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.users') ? 'bg-red-500 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5' }} transition text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Users
                    </a>
                    <a href="{{ route('admin.rooms') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.rooms') ? 'bg-red-500 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5' }} transition text-sm font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
                        Rooms
                    </a>
                </nav>
            </aside>
            <main class="flex-1 min-w-0">
                {{ $slot }}
            </main>
        </div>
    </div>
    </div>

    <script>document.addEventListener('alpine:init',()=>{Alpine.store('sidebar',{expanded:localStorage.getItem('sidebarExpanded')!=='false',toggle(){this.expanded=!this.expanded;localStorage.setItem('sidebarExpanded',this.expanded)}})})</script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
