<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('mentorship_sessions')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->enum('visibility', ['partagee', 'privee'])->default('partagee');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_notes');
    }
};
