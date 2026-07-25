<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_document_path')->nullable()->after('avatar_path');
            $table->timestamp('identity_verified_at')->nullable()->after('identity_document_path');
            $table->foreignId('identity_verified_by')->nullable()->after('identity_verified_at')->constrained('users')->nullOnDelete();
            $table->text('identity_rejected_reason')->nullable()->after('identity_verified_by');
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('active')->change();
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY status ENUM('pending', 'active', 'suspended', 'rejected') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        DB::table('users')->where('status', 'rejected')->update(['status' => 'pending']);

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['pending', 'active', 'suspended'])->default('active')->change();
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY status ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'active'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['identity_verified_by']);
            $table->dropColumn([
                'identity_document_path', 'identity_verified_at', 'identity_verified_by', 'identity_rejected_reason',
            ]);
        });
    }
};
