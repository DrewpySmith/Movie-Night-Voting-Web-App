<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\MovieController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\VoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        Route::get('rooms', [RoomController::class, 'index']);
        Route::post('rooms', [RoomController::class, 'store']);
        Route::get('rooms/{room}', [RoomController::class, 'show']);
        Route::put('rooms/{room}', [RoomController::class, 'update']);
        Route::delete('rooms/{room}', [RoomController::class, 'destroy']);
        Route::post('rooms/{room}/join', [RoomController::class, 'join']);
        Route::post('rooms/{room}/leave', [RoomController::class, 'leave']);
        Route::post('rooms/join-by-code', [RoomController::class, 'joinByCode']);
        Route::post('rooms/{room}/close', [RoomController::class, 'close']);
        Route::post('rooms/{room}/regenerate-code', [RoomController::class, 'regenerateCode']);

        Route::get('movies/search', [MovieController::class, 'search'])->middleware('throttle:movies');
        Route::get('movies/fetch/{omdbId}', [MovieController::class, 'fetch'])->middleware('throttle:movies');
        Route::get('movies/{movie}', [MovieController::class, 'show']);
        Route::get('trending', [MovieController::class, 'trending'])->middleware('throttle:movies');

        Route::post('rooms/{room}/movies', [MovieController::class, 'suggest']);
        Route::delete('rooms/{room}/movies/{movie}', [MovieController::class, 'remove']);

        Route::post('rooms/{room}/movies/{movie}/vote', [VoteController::class, 'store']);
        Route::delete('rooms/{room}/movies/{movie}/vote', [VoteController::class, 'destroy']);
        Route::get('rooms/{room}/votes', [VoteController::class, 'index']);
        Route::get('rooms/{room}/winner', [VoteController::class, 'winner']);
        Route::post('rooms/{room}/declare-winner/{movie}', [VoteController::class, 'declareWinner']);

        Route::get('rooms/{room}/comments', [CommentController::class, 'index']);
        Route::post('rooms/{room}/comments', [CommentController::class, 'store']);
        Route::delete('rooms/{room}/comments/{comment}', [CommentController::class, 'destroy']);

        Route::post('rooms/{room}/invite', [InvitationController::class, 'invite']);
        Route::get('invitations', [InvitationController::class, 'index']);
        Route::post('invitations/{token}/accept', [InvitationController::class, 'accept']);
        Route::post('invitations/{token}/decline', [InvitationController::class, 'decline']);
    });
});
