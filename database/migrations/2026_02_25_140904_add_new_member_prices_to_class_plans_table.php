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
        Schema::table('class_plans', function (Blueprint $table) {
            // New member prices (shown on public booking subdomain)
            $table->json('new_member_prices')->nullable()->after('drop_in_prices');
            $table->json('new_member_drop_in_prices')->nullable()->after('new_member_prices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->dropColumn(['new_member_prices', 'new_member_drop_in_prices']);
        });
    }
};