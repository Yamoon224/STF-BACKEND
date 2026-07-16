<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_pairings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['en_attente', 'actif', 'pause', 'termine'])->default('en_attente');
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->timestamp('matched_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_pairings');
    }
};
