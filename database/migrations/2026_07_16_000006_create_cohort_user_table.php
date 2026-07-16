<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohort_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role_in_cohort', ['mentee', 'mentor']);
            $table->timestamps();
            $table->unique(['cohort_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohort_user');
    }
};
