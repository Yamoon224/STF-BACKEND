<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('provider')->nullable();
            $table->text('description')->nullable();
            $table->string('amount')->nullable();
            $table->string('audience')->nullable();
            $table->date('deadline')->nullable();
            $table->string('application_url')->nullable();
            $table->string('image_path')->nullable();
            $table->enum('status', ['ouverte', 'fermee', 'a_venir'])->default('ouverte');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
