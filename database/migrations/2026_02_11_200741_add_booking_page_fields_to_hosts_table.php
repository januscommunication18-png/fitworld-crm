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
        Schema::table('hosts', function (Blueprint $table) {
            // Only add columns that don't exist yet
            // booking_page_status and cover_image_path already exist
            if (!Schema::hasColumn('hosts', 'show_address')) {
                $table->boolean('show_address')->default(true)->after('booking_settings');
            }
            if (!Schema::hasColumn('hosts', 'show_social_links')) {
                $table->boolean('show_social_links')->default(true)->after('show_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn([
                'show_address',
                'show_social_links',
            ]);
        });
    }
};
