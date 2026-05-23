<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movie_rooms', function (Blueprint $table) {
            $table->foreign('winner_movie_id')->references('id')->on('movies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movie_rooms', function (Blueprint $table) {
            $table->dropForeign(['winner_movie_id']);
        });
    }
};
