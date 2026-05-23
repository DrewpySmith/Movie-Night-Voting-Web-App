<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('visibility')->default('public');
            $table->string('invite_code', 6)->unique()->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('open');
            $table->unsignedBigInteger('winner_movie_id')->nullable();
            $table->timestamps();

            $table->index('host_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_rooms');
    }
};
