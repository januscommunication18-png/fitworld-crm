<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('mobile_service_area')->nullable()->after('hide_link_until_booking');
            $table->text('mobile_travel_notes')->nullable()->after('mobile_service_area');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['mobile_service_area', 'mobile_travel_notes']);
        });
    }
};
