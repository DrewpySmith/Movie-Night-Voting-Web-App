<?php

namespace App\Console\Commands;

use App\Enums\RoomStatus;
use App\Models\MovieRoom;
use App\Services\VotingService;
use Illuminate\Console\Command;

class CheckRoomWinners extends Command
{
    protected $signature = 'rooms:check-winners';
    protected $description = 'Check scheduled rooms past their time and calculate winners';

    public function handle(VotingService $votingService): void
    {
        $rooms = MovieRoom::where('status', RoomStatus::Open->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($rooms as $room) {
            $winner = $votingService->calculateWinner($room);

            if ($winner) {
                $this->info("Winner for '{$room->title}': {$winner->title}");
            } else {
                $room->update(['status' => RoomStatus::Closed->value]);
                $this->info("Room '{$room->title}' closed — no winner (no votes)");
            }
        }

        if ($rooms->isEmpty()) {
            $this->info('No rooms to process.');
        }
    }
}
