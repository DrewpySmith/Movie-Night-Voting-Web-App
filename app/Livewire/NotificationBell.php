<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $notifications = [];
    public bool $showDropdown = false;

    protected function getListeners(): array
    {
        $userId = auth()->id();
        return [
            "echo-private:App.Models.User.{$userId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'refreshNotifications',
            'notification-marked-read' => 'refreshNotifications',
        ];
    }

    public function mount(): void
    {
        $this->refreshNotifications();
    }

    public function refreshNotifications(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->unreadCount = $user->unreadNotifications()->count();
        $this->notifications = $user->notifications()
            ->take(10)
            ->get()
            ->map(function (DatabaseNotification $n) {
                return [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'unknown',
                    'message' => $n->data['message'] ?? '',
                    'action_url' => $n->data['action_url'] ?? '#',
                    'read' => $n->read_at !== null,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);

        $this->refreshNotifications();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->refreshNotifications();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
