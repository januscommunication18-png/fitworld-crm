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
        // Add prices JSON to class_plans table
        Schema::table('class_plans', function (Blueprint $table) {
            $table->json('prices')->nullable()->after('drop_in_price');
            $table->json('drop_in_prices')->nullable()->after('prices');
        });

        // Add prices JSON to service_plans table
        Schema::table('service_plans', function (Blueprint $table) {
            $table->json('prices')->nullable()->after('price');
            $table->json('deposit_prices')->nullable()->after('deposit_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->dropColumn(['prices', 'drop_in_prices']);
        });

        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropColumn(['prices', 'deposit_prices']);
        });
    }
};
