<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Billing discounts structure:
     * {
     *     "1": 0,      // 1 month: 0% discount
     *     "3": 5,      // 3 months: 5% discount
     *     "6": 10,     // 6 months: 10% discount
     *     "9": 15,     // 9 months: 15% discount
     *     "12": 20     // 12 months: 20% discount
     * }
     */
    public function up(): void
    {
        Schema::table('service_plans', function (Blueprint $table) {
            $table->json('billing_discounts')->nullable()->after('cancellation_hours');
        });

        Schema::table('class_plans', function (Blueprint $table) {
            $table->json('billing_discounts')->nullable()->after('drop_in_prices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropColumn('billing_discounts');
        });

        Schema::table('class_plans', function (Blueprint $table) {
            $table->dropColumn('billing_discounts');
        });
    }
};
