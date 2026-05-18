<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->json('custom_rental_item_categories')->nullable()->after('disabled_service_plan_categories');
            $table->json('disabled_rental_item_categories')->nullable()->after('custom_rental_item_categories');
        });
    }

    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn(['custom_rental_item_categories', 'disabled_rental_item_categories']);
        });
    }
};
