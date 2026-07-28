<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->string('tshirt_size', 3)->nullable()->after('payment_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn('tshirt_size');
        });
    }
};
