<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentee_profiles', function (Blueprint $table) {
            $table->string('diploma_document_path')->nullable()->after('goals');
        });
    }

    public function down(): void
    {
        Schema::table('mentee_profiles', function (Blueprint $table) {
            $table->dropColumn('diploma_document_path');
        });
    }
};
