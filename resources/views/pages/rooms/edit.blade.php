<x-app-layout>
    <x-slot name="title">Edit {{ $room->title }}</x-slot>

    <div class="max-w-2xl mx-auto px-5 py-12">
        <div class="rounded-[2rem] border border-white/10 bg-surface p-8 shadow-2xl shadow-black/30">
            <h1 class="text-3xl font-black text-white">Edit Room</h1>
            <p class="mt-2 text-zinc-400">Update your room details.</p>

            <form method="POST" action="{{ route('rooms.update', $room) }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="title" class="block text-sm font-bold text-zinc-300 mb-1">Room Title</label>
                    <input id="title" name="title" value="{{ old('title', $room->title) }}" required class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">
                    @error('title') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-bold text-zinc-300 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3" class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400">{{ old('description', $room->description) }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="scheduled_at" class="block text-sm font-bold text-zinc-300 mb-1">Schedule Close (optional)</label>
                    <input id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at', $room->scheduled_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-2xl border border-white/10 bg-surface-elevated px-4 py-4 text-white outline-none transition focus:border-red-400 [color-scheme:dark]">
                    @error('scheduled_at') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-2xl bg-red-500 py-4 text-base font-black text-white transition hover:bg-red-400">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
