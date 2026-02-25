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
        // Add new member prices to service_plans table
        Schema::table('service_plans', function (Blueprint $table) {
            $table->json('new_member_prices')->nullable()->after('deposit_prices');
            $table->json('new_member_deposit_prices')->nullable()->after('new_member_prices');
        });

        // Add new member prices to membership_plans table
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->json('new_member_prices')->nullable()->after('prices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropColumn(['new_member_prices', 'new_member_deposit_prices']);
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn('new_member_prices');
        });
    }
};
