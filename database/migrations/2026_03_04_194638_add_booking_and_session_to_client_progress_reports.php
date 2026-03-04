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
        Schema::table('client_progress_reports', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('progress_template_id')->constrained()->nullOnDelete();
            $table->foreignId('class_session_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();

            $table->index('booking_id', 'cpr_booking_index');
            $table->index('class_session_id', 'cpr_session_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_progress_reports', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropForeign(['class_session_id']);
            $table->dropIndex('cpr_booking_index');
            $table->dropIndex('cpr_session_index');
            $table->dropColumn(['booking_id', 'class_session_id']);
        });
    }
};
