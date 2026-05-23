<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('omdb_id')->unique();
            $table->string('title');
            $table->string('year', 10)->nullable();
            $table->text('genre')->nullable();
            $table->text('plot')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('runtime')->nullable();
            $table->string('imdb_rating', 10)->nullable();
            $table->text('actors')->nullable();
            $table->string('director')->nullable();
            $table->string('trailer_url')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamps();

            $table->index('omdb_id');
        });

        Schema::create('room_movies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('movie_rooms')->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->foreignId('suggested_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['room_id', 'movie_id']);
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_movies');
        Schema::dropIfExists('movies');
    }
};
