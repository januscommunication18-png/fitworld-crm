<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Branded client app (white-label) per studio: the app token baked into
     * each studio's mobile build (indexed — checked on every client API
     * request) and the JSON blob holding the app's branding/config.
     */
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->string('client_app_token', 64)->nullable()->unique()->after('member_portal_settings');
            $table->json('client_app_settings')->nullable()->after('client_app_token');
        });
    }

    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn(['client_app_token', 'client_app_settings']);
        });
    }
};
