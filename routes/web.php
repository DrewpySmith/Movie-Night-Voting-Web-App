<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InvitationController as WebInvitationController;
use App\Http\Controllers\Web\RoomController;
use App\Http\Controllers\Web\ProfileController as WebProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::post('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::post('/rooms/{room}/close', [RoomController::class, 'close'])->name('rooms.close');
    Route::post('/rooms/{room}/regenerate-code', [RoomController::class, 'regenerateCode'])->name('rooms.regenerate-code');
    Route::post('/rooms/{room}/invite', [RoomController::class, 'invite'])->name('rooms.invite');
    Route::post('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
    Route::get('/rooms/{room}/results', [RoomController::class, 'results'])->name('rooms.results');
    Route::post('/rooms/{room}/transfer', [RoomController::class, 'transfer'])->name('rooms.transfer');

    Route::get('/profile', [WebProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [WebProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [WebProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/delete', [WebProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/invitations/{token}/accept', [WebInvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{token}/decline', [WebInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::middleware(['auth', 'verified', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/rooms', [AdminController::class, 'rooms'])->name('rooms');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::delete('/rooms/{room}', [AdminController::class, 'deleteRoom'])->name('rooms.delete');
    Route::post('/rooms/{id}/restore', [AdminController::class, 'restoreRoom'])->name('rooms.restore');
    Route::delete('/comments/{comment}', [AdminController::class, 'deleteComment'])->name('comments.delete');
    Route::post('/comments/{id}/restore', [AdminController::class, 'restoreComment'])->name('comments.restore');
});

require __DIR__.'/auth.php';
