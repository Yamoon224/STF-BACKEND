<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('audience')->nullable();
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->enum('status', ['a_venir', 'en_cours', 'archive'])->default('a_venir');
            $table->date('cycle_start')->nullable();
            $table->date('cycle_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
