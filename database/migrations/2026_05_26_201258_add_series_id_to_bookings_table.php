<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Identifier shared by every Booking that came from the same
            // series purchase. Format: "TX-{transaction.id}" by convention.
            // Null for one-off / single bookings.
            $table->string('series_id', 50)->nullable()->after('booking_type');
            $table->index(['host_id', 'series_id'], 'bookings_host_series_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_host_series_idx');
            $table->dropColumn('series_id');
        });
    }
};
