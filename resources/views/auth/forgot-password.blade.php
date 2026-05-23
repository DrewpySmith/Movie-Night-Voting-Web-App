<x-guest-layout>
    <div class="grid min-h-[calc(100vh-72px)] place-items-center px-5 py-12">
        <div class="w-full max-w-md animate-rise rounded-[2rem] border border-white/10 bg-surface p-8 shadow-2xl shadow-black/30">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-red-500 text-2xl font-black text-white">MN</div>
            <h1 class="mt-6 text-center text-3xl font-black text-white">Forgot password?</h1>
            <p class="mt-2 text-center text-zinc-400">No worries. We'll send you a reset link.</p>

            <x-auth-session-status class="mb-4 text-green-400 text-sm" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
                @csrf

                <div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="Email"
                           class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                    @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-2xl bg-red-500 py-4 text-base font-black text-white transition hover:bg-red-400">
                    Send Reset Link
                </button>

                <p class="text-center text-sm text-zinc-500">
                    <a href="{{ route('login') }}" class="text-zinc-400 hover:text-white transition">Back to login</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>
