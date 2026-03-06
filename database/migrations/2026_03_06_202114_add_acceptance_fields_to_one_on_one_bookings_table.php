<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('one_on_one_bookings', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('booked_at');
            $table->timestamp('declined_at')->nullable()->after('confirmed_at');
            $table->string('decline_reason', 500)->nullable()->after('declined_at');
        });

        // Update existing confirmed bookings to have confirmed_at set
        DB::table('one_on_one_bookings')
            ->where('status', 'confirmed')
            ->whereNull('confirmed_at')
            ->update(['confirmed_at' => DB::raw('booked_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_on_one_bookings', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'declined_at', 'decline_reason']);
        });
    }
};
