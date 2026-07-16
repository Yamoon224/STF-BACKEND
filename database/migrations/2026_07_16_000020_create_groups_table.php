<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['automatique', 'travail', 'mentorat'])->default('travail');
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['en_validation', 'actif', 'archive'])->default('en_validation');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
