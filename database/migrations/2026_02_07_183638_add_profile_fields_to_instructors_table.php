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
        Schema::table('instructors', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('photo_path')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('photo_path');
            $table->json('specialties')->nullable()->after('bio');
            $table->text('certifications')->nullable()->after('specialties');
            $table->json('social_links')->nullable()->after('certifications');
            $table->boolean('is_visible')->default(true)->after('social_links');
            $table->boolean('is_active')->default(true)->after('is_visible');

            // Rename invite_status to status for consistency
            $table->renameColumn('invite_status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->renameColumn('status', 'invite_status');
            $table->dropColumn([
                'phone',
                'photo_path',
                'bio',
                'specialties',
                'certifications',
                'social_links',
                'is_visible',
                'is_active',
            ]);
        });
    }
};
