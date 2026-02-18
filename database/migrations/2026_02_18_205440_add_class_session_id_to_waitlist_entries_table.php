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
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->foreignId('class_session_id')->nullable()->after('class_plan_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->dropForeign(['class_session_id']);
            $table->dropColumn('class_session_id');
        });
    }
};
