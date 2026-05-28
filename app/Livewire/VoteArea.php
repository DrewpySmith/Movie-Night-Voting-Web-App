<?php

namespace App\Livewire;

use App\Enums\NotificationType;
use App\Enums\VoteType;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Notifications\RoomNotification;
use App\Services\VotingService;
use Livewire\Component;

class VoteArea extends Component
{
    public MovieRoom $room;
    public ?Movie $movie = null;
    public array $votes = [];
    public ?int $winnerId = null;

    protected VotingService $votingService;

    public function boot(VotingService $votingService): void
    {
        $this->votingService = $votingService;
    }

    public function mount(): void
    {
        $this->loadVotes();
    }

    public function vote(int $movieId, string $voteType): void
    {
        $movie = Movie::findOrFail($movieId);

        $this->votingService->castVote(
            $this->room,
            $movie,
            auth()->user(),
            VoteType::from($voteType)
        );

        $this->loadVotes();
        $this->dispatch('vote-cast', movieId: $movieId);
    }

    public function removeVote(int $movieId): void
    {
        $movie = Movie::findOrFail($movieId);
        $this->votingService->removeVote($this->room, $movie, auth()->user());
        $this->loadVotes();
    }

    public function calculateWinner(): void
    {
        $winner = $this->votingService->calculateWinner($this->room);
        $this->winnerId = $winner?->id;
        $this->dispatch('winner-selected', movieId: $this->winnerId);

        if ($winner) {
            $this->room->members->each(function ($member) use ($winner) {
                if ($member->id !== auth()->id()) {
                    $member->notify(new RoomNotification(
                        NotificationType::WinnerDeclared,
                        [
                            'message' => $winner->title . ' won in ' . $this->room->title,
                            'room_id' => $this->room->id,
                            'movie_title' => $winner->title,
                            'room_title' => $this->room->title,
                            'action_url' => '/rooms/' . $this->room->id,
                        ]
                    ));
                }
            });
        }
    }

    protected function loadVotes(): void
    {
        $this->votes = $this->votingService->getVoteTally($this->room);
        $this->winnerId = $this->room->winner_movie_id;
    }

    public function render()
    {
        $roomMovies = $this->movie
            ? collect([$this->movie->load('votes')])
            : $this->room->movies()->with('votes')->get();

        return view('livewire.vote-area', [
            'roomMovies' => $roomMovies,
        ]);
    }
}
