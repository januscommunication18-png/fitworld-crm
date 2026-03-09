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
        Schema::table('one_on_one_invites', function (Blueprint $table) {
            // Store multiple dates with multiple time slots as JSON
            // Format: { "2026-03-17": ["09:00", "10:00"], "2026-03-18": ["14:00"] }
            $table->json('scheduled_slots')->nullable()->after('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_on_one_invites', function (Blueprint $table) {
            $table->dropColumn('scheduled_slots');
        });
    }
};
