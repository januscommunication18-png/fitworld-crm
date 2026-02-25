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
        Schema::table('hosts', function (Blueprint $table) {
            // Language settings for the studio
            $table->string('default_language_app', 5)->default('en')->after('operating_countries');
            $table->string('default_language_booking', 5)->default('en')->after('default_language_app');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn(['default_language_app', 'default_language_booking']);
        });
    }
};
