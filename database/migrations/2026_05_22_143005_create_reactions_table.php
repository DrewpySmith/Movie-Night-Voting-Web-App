<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->foreignId('movie_id')->nullable()->constrained('movies')->cascadeOnDelete();
            $table->string('reaction');
            $table->timestamps();

            $table->index('user_id');
            $table->index('comment_id');
            $table->index('movie_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
