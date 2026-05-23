<x-guest-layout>
    <div class="grid min-h-[calc(100vh-72px)] place-items-center px-5 py-12">
        <div class="w-full max-w-md animate-rise rounded-[2rem] border border-white/10 bg-surface p-8 shadow-2xl shadow-black/30">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-red-500 text-2xl font-black text-white">MN</div>
            <h1 class="mt-6 text-center text-3xl font-black text-white">Confirm password</h1>
            <p class="mt-2 text-center text-zinc-400">This is a secure area. Please confirm your password before continuing.</p>

            <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-4">
                @csrf

                <div>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           placeholder="Password"
                           class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                    @error('password') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-2xl bg-red-500 py-4 text-base font-black text-white transition hover:bg-red-400">
                    Confirm
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
