<x-guest-layout>
    <div class="grid min-h-[calc(100vh-72px)] place-items-center px-5 py-12">
        <div class="w-full max-w-md animate-rise rounded-[2rem] border border-white/10 bg-surface p-8 shadow-2xl shadow-black/30">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-red-500 text-2xl font-black text-white">MN</div>
            <h1 class="mt-6 text-center text-3xl font-black text-white">Create account</h1>
            <p class="mt-2 text-center text-zinc-400">Join the movie night community.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
                @csrf

                <div>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           placeholder="Name"
                           class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                           placeholder="Email"
                           class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                    @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           placeholder="Password"
                           class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                    @error('password') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           placeholder="Confirm Password"
                           class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                </div>

                <button type="submit" class="w-full rounded-2xl bg-red-500 py-4 text-base font-black text-white transition hover:bg-red-400">
                    Register
                </button>

                <p class="text-center text-sm text-zinc-500">
                    Already registered?
                    <a href="{{ route('login') }}" class="text-red-400 font-bold hover:text-red-300">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>
