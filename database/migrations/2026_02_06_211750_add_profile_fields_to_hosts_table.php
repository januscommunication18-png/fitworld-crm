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
            // Public contact info
            $table->string('studio_email')->nullable()->after('phone');

            // Cover/banner image
            $table->string('cover_image_path')->nullable()->after('logo_path');

            // Social links (JSON: instagram, facebook, website, tiktok)
            $table->json('social_links')->nullable()->after('about');

            // Internal contact
            $table->string('contact_name')->nullable()->after('social_links');
            $table->string('support_email')->nullable()->after('contact_name');

            // Currency & Country
            $table->string('country', 2)->default('US')->after('city');
            $table->string('currency', 3)->default('USD')->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn([
                'studio_email',
                'cover_image_path',
                'social_links',
                'contact_name',
                'support_email',
                'country',
                'currency',
            ]);
        });
    }
};
