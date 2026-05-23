<x-guest-layout>
    <div class="grid min-h-[calc(100vh-72px)] place-items-center px-5 py-12">
        <div class="w-full max-w-md animate-rise rounded-[2rem] border border-white/10 bg-surface p-8 shadow-2xl shadow-black/30 text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-red-500 text-2xl font-black text-white">MN</div>
            <h1 class="mt-6 text-3xl font-black text-white">Verify email</h1>
            <p class="mt-2 text-zinc-400">{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</p>

            @if (session('status') == 'verification-link-sent')
                <p class="mt-4 text-sm text-green-400 font-medium">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </p>
            @endif

            <div class="mt-8 flex items-center justify-between gap-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="rounded-2xl bg-red-500 px-6 py-4 text-sm font-black text-white transition hover:bg-red-400">
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-2xl border border-white/10 bg-white/10 px-6 py-4 text-sm font-bold text-white transition hover:bg-white/15">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
