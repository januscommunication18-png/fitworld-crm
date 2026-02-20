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
        Schema::table('membership_plans', function (Blueprint $table) {
            // Addon members - how many additional people can the member bring
            $table->unsignedTinyInteger('addon_members')->default(0)->after('credits_per_cycle');

            // Free amenities included with this membership
            $table->json('free_amenities')->nullable()->after('addon_members');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn(['addon_members', 'free_amenities']);
        });
    }
};
