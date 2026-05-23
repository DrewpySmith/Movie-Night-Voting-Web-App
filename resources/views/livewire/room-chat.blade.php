<div>
    <section class="rounded-[1.5rem] bg-surface p-6">
        <h2 class="text-xl font-black text-white">Comments</h2>
        <div class="mt-5 space-y-3 max-h-72 overflow-y-auto">
            @forelse($comments as $comment)
                <div class="rounded-2xl bg-surface-elevated p-4">
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="font-bold text-red-400">{{ $comment->user?->name }}</span>
                        <span class="text-zinc-500">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-2 text-zinc-100 text-sm">{{ $comment->body }}</p>
                    @if($comment->user_id === auth()->id() || auth()->user()?->is_admin)
                        <button wire:click="delete({{ $comment->id }})" class="text-xs text-red-400 hover:text-red-300 mt-2 font-bold">Delete</button>
                    @endif
                </div>
            @empty
                <p class="text-zinc-500 text-sm text-center py-4">No comments yet</p>
            @endforelse
        </div>

        <form wire:submit="submit" class="mt-5 flex gap-2">
            <input type="text" wire:model="body" placeholder="Add a comment..."
                   class="min-w-0 flex-1 rounded-xl border border-white/10 bg-surface-elevated px-4 py-3 text-sm text-white outline-none transition focus:border-red-400/70">
            <button type="submit" class="rounded-xl bg-red-500 px-5 text-sm font-bold text-white transition hover:bg-red-400">Send</button>
        </form>
        @error('body') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
    </section>
</div>
