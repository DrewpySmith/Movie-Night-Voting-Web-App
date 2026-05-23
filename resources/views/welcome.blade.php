<x-guest-layout>
    <section class="relative isolate min-h-[calc(100vh-72px)] overflow-hidden">
        <div class="absolute inset-0 -z-20">
            <div class="hero-strip hero-strip-left">
                @for($i = 0; $i < 3; $i++)
                    @foreach(range(1, 6) as $j)
                        <div class="w-full aspect-[2/3] bg-surface rounded-xl"></div>
                    @endforeach
                @endfor
            </div>
            <div class="hero-strip hero-strip-right hidden sm:flex">
                @for($i = 0; $i < 3; $i++)
                    @foreach(range(1, 6) as $j)
                        <div class="w-full aspect-[2/3] bg-surface rounded-xl"></div>
                    @endforeach
                @endfor
            </div>
        </div>
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_15%,rgba(239,68,68,0.28),transparent_36%),linear-gradient(90deg,rgba(9,9,11,0.94),rgba(9,9,11,0.7),rgba(9,9,11,0.94))]" />
        <div class="mx-auto flex min-h-[calc(100vh-72px)] max-w-6xl flex-col items-center justify-center px-5 py-20 text-center">
            <p class="mb-5 text-sm font-bold uppercase tracking-[0.5em] text-red-400 animate-rise">Movie Night</p>
            <h1 class="max-w-5xl text-5xl font-black tracking-[-0.08em] text-white sm:text-7xl lg:text-8xl animate-rise animation-delay-100">
                Pick the movie before the group chat loses the plot.
            </h1>
            <p class="mt-7 max-w-2xl text-lg leading-8 text-zinc-300 sm:text-xl animate-rise animation-delay-200">
                Create a room, suggest films, vote with friends, and crown a winner with a cleaner cinematic interface.
            </p>
            <div class="mt-10 flex flex-col gap-3 sm:flex-row animate-rise animation-delay-300">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-red-500 px-8 py-4 text-base font-extrabold text-white shadow-2xl shadow-red-950/50 transition hover:-translate-y-1 hover:bg-red-400">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="rounded-full bg-red-500 px-8 py-4 text-base font-extrabold text-white shadow-2xl shadow-red-950/50 transition hover:-translate-y-1 hover:bg-red-400">
                        Start Voting
                    </a>
                    <a href="{{ route('login') }}" class="rounded-full border border-white/15 bg-white/10 px-8 py-4 text-base font-bold text-white backdrop-blur transition hover:-translate-y-1 hover:bg-white/15">
                        Browse Movies
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-5 pb-20">
        <div class="mt-20 mb-16">
            <h2 class="text-3xl font-black tracking-tight text-white text-center mb-2">Trending Movies</h2>
            <p class="text-zinc-400 text-center mb-6">Most popular movies this week</p>
            <livewire:trending-movies />
        </div>
    </div>
</x-guest-layout>
