<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\MovieRoom;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    public function index(Request $request, MovieRoom $room): AnonymousResourceCollection
    {
        abort_unless($room->isMember($request->user()), 403);

        $comments = Comment::where('room_id', $room->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, MovieRoom $room): CommentResource
    {
        abort_unless($room->isMember($request->user()), 403);

        $comment = Comment::create([
            'room_id' => $room->id,
            'movie_id' => $request->input('movie_id'),
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        return CommentResource::make($comment->load('user'));
    }

    public function destroy(MovieRoom $room, Comment $comment): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
