<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // 'single' = one-off session booking, 'series' = one of many bookings
            // created together for a class_plan series purchase. Set at booking-
            // creation time from the transaction's class_booking_type metadata.
            $table->string('booking_type', 20)->default('single')->after('bookable_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('booking_type');
        });
    }
};
