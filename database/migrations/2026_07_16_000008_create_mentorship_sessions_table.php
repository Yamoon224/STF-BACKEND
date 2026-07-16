<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Named "mentorship_sessions" (not "sessions") to avoid colliding with
        // Laravel's own session-driver table.
        Schema::create('mentorship_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pairing_id')->constrained('mentorship_pairings')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->enum('status', ['en_attente', 'confirmee', 'realisee', 'annulee'])->default('en_attente');
            $table->string('topic')->nullable();
            $table->string('location_or_link')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_sessions');
    }
};
