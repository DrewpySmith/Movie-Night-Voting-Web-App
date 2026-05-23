<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'users' => User::count(),
            'rooms' => MovieRoom::withTrashed()->count(),
            'comments' => Comment::withTrashed()->count(),
            'open_rooms' => MovieRoom::where('status', 'open')->count(),
            'deleted_rooms' => MovieRoom::onlyTrashed()->count(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentRooms = MovieRoom::with('host')->withCount('members')->latest()->take(5)->get();

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('D');
            $chartData[] = MovieRoom::whereDate('created_at', $date)->count();
        }

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentRooms', 'chartLabels', 'chartData'));
    }

    public function users(Request $request): View
    {
        $users = User::withCount('hostedRooms')
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function rooms(Request $request): View
    {
        $rooms = MovieRoom::query()
            ->with('host')
            ->withCount('members', 'movies', 'votes')
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed())
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhereHas('host', fn($q) => $q->where('name', 'like', "%{$s}%"));
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.rooms', compact('rooms'));
    }

    public function deleteUser(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users')->with('status', 'User deleted');
    }

    public function deleteRoom(MovieRoom $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('admin.rooms')->with('status', 'Room moved to trash');
    }

    public function restoreRoom(int $id): RedirectResponse
    {
        $room = MovieRoom::withTrashed()->findOrFail($id);
        $room->restore();

        return redirect()->route('admin.rooms')->with('status', 'Room restored');
    }

    public function deleteComment(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()->back()->with('status', 'Comment moved to trash');
    }

    public function restoreComment(int $id): RedirectResponse
    {
        $comment = Comment::withTrashed()->findOrFail($id);
        $comment->restore();

        return redirect()->back()->with('status', 'Comment restored');
    }
}
