<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'suspended'])->default('active')->after('password');
            $table->string('country')->nullable()->after('status');
            $table->string('phone')->nullable()->after('country');
            $table->string('locale', 5)->default('fr')->after('phone');
            $table->string('avatar_path')->nullable()->after('locale');
            $table->boolean('mfa_enabled')->default(false)->after('avatar_path');
            $table->text('mfa_secret')->nullable()->after('mfa_enabled');
            $table->text('mfa_recovery_codes')->nullable()->after('mfa_secret');
            $table->timestamp('last_login_at')->nullable()->after('mfa_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status', 'country', 'phone', 'locale', 'avatar_path',
                'mfa_enabled', 'mfa_secret', 'mfa_recovery_codes', 'last_login_at',
            ]);
        });
    }
};
