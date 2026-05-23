<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('movie_rooms')->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->string('invitee_email')->nullable();
            $table->foreignId('invitee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token')->unique();
            $table->string('status')->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('invitee_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
