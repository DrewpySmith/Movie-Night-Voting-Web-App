<x-app-layout>
    <x-slot name="title">Profile</x-slot>

    <div class="max-w-2xl mx-auto px-5 py-8 sm:px-8 lg:px-12 space-y-6">
        @if(session('status'))
            <div class="rounded-2xl bg-green-900/50 px-5 py-4 text-sm font-bold text-green-300">{{ session('status') }}</div>
        @endif

        <div class="rounded-[1.5rem] border border-white/10 bg-surface p-6 sm:p-8">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <h3 class="text-lg font-black text-white">Profile Information</h3>

                <div>
                    <label for="name" class="block text-sm font-bold text-zinc-300 mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-zinc-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                </div>

                <div>
                    <label for="avatar" class="block text-sm font-bold text-zinc-300 mb-2">Avatar</label>
                    <div class="flex items-center gap-4 mb-3">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="" class="h-16 w-16 rounded-full object-cover">
                        @else
                            <div class="grid h-16 w-16 place-items-center rounded-full bg-surface-elevated text-2xl font-black text-white">{{ substr($user->name, 0, 1) }}</div>
                        @endif
                        <span class="text-sm text-zinc-400">{{ $user->avatar ? 'Current avatar' : 'No avatar set' }}</span>
                    </div>
                    <input type="file" name="avatar" id="avatar" class="w-full text-zinc-300 file:mr-4 file:rounded-xl file:border-0 file:bg-surface-elevated file:px-4 file:py-3 file:text-sm file:font-bold file:text-white hover:file:bg-white/10">
                </div>

                <button type="submit" class="w-full rounded-2xl bg-red-500 py-4 text-base font-black text-white transition hover:bg-red-400">Update Profile</button>
            </form>
        </div>

        <div class="rounded-[1.5rem] border border-white/10 bg-surface p-6 sm:p-8">
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                @csrf
                <h3 class="text-lg font-black text-white">Change Password</h3>

                <div>
                    <label for="current_password" class="block text-sm font-bold text-zinc-300 mb-2">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-zinc-300 mb-2">New Password</label>
                    <input type="password" name="password" id="password" class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-zinc-300 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                </div>

                <button type="submit" class="w-full rounded-2xl bg-red-500 py-4 text-base font-black text-white transition hover:bg-red-400">Update Password</button>
            </form>
        </div>

        <div class="rounded-[1.5rem] border border-red-900/30 bg-surface p-6 sm:p-8">
            <h3 class="text-lg font-black text-white">Delete Account</h3>
            <p class="mt-2 text-sm text-zinc-400">Once your account is deleted, all of its resources and data will be permanently deleted.</p>

            <form method="POST" action="{{ route('profile.destroy') }}" class="mt-6" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.')">
                @csrf
                <div class="mb-4">
                    <label for="delete_password" class="block text-sm font-bold text-zinc-300 mb-2">Enter your password to confirm</label>
                    <input type="password" name="password" id="delete_password" required class="w-full rounded-2xl border border-red-900/30 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                </div>
                <button type="submit" class="w-full rounded-2xl bg-red-950/80 py-4 text-base font-black text-red-200 transition hover:bg-red-900">Delete Account</button>
            </form>
        </div>
    </div>
</x-app-layout>
