<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\MovieRoom;
use Livewire\Component;
use Livewire\WithPagination;

class RoomChat extends Component
{
    use WithPagination;

    public MovieRoom $room;
    public ?int $movieId = null;
    public string $body = '';

    protected $rules = [
        'body' => ['required', 'string', 'min:1', 'max:1000'],
    ];

    public function submit(): void
    {
        $this->validate();

        Comment::create([
            'room_id' => $this->room->id,
            'movie_id' => $this->movieId,
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $this->body = '';
        $this->dispatch('comment-added');
    }

    public function delete(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        if ($comment->user_id !== auth()->id() && !auth()->user()->is_admin) {
            return;
        }

        $comment->delete();
        $this->dispatch('comment-deleted');
    }

    public function render()
    {
        $comments = Comment::where('room_id', $this->room->id)
            ->when($this->movieId, fn($q) => $q->where('movie_id', $this->movieId))
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('livewire.room-chat', compact('comments'));
    }
}
