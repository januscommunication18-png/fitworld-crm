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
            if (!Schema::hasColumn('hosts', 'booking_page_status')) {
                $table->enum('booking_page_status', ['draft', 'published'])->default('draft')->after('subscription_ends_at');
            }
            if (!Schema::hasColumn('hosts', 'cover_image_path')) {
                $table->string('cover_image_path')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('hosts', 'show_address')) {
                $table->boolean('show_address')->default(true)->after('booking_page_status');
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
            $table->dropColumn(['booking_page_status', 'cover_image_path', 'show_address', 'show_social_links']);
        });
    }
};
