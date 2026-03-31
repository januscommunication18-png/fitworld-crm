<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: These columns may already exist if added manually or through a fresh install.
     * The migration uses conditional checks to avoid errors on duplicate columns.
     */
    public function up(): void
    {
        // Post-signup onboarding tracking
        if (!Schema::hasColumn('hosts', 'post_signup_step')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->unsignedTinyInteger('post_signup_step')->default(1)->after('setup_completed_at');
            });
        }
        if (!Schema::hasColumn('hosts', 'post_signup_completed_at')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->timestamp('post_signup_completed_at')->nullable()->after('post_signup_step');
            });
        }

        // Tech support request tracking
        if (!Schema::hasColumn('hosts', 'tech_support_requested')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->boolean('tech_support_requested')->default(false)->after('post_signup_completed_at');
            });
        }
        if (!Schema::hasColumn('hosts', 'tech_support_requested_at')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->timestamp('tech_support_requested_at')->nullable()->after('tech_support_requested');
            });
        }
        if (!Schema::hasColumn('hosts', 'tech_support_completed_at')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->timestamp('tech_support_completed_at')->nullable()->after('tech_support_requested_at');
            });
        }
        if (!Schema::hasColumn('hosts', 'tech_support_ticket_id')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->unsignedBigInteger('tech_support_ticket_id')->nullable()->after('tech_support_completed_at');
                $table->index('tech_support_ticket_id');
            });
        }

        // Owner phone verification
        if (!Schema::hasColumn('hosts', 'owner_phone_number')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->string('owner_phone_number', 20)->nullable()->after('phone');
            });
        }
        if (!Schema::hasColumn('hosts', 'owner_phone_country_code')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->string('owner_phone_country_code', 5)->nullable()->after('owner_phone_number');
            });
        }
        if (!Schema::hasColumn('hosts', 'owner_phone_verified')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->boolean('owner_phone_verified')->default(false)->after('owner_phone_country_code');
            });
        }
        if (!Schema::hasColumn('hosts', 'owner_phone_verified_at')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->timestamp('owner_phone_verified_at')->nullable()->after('owner_phone_verified');
            });
        }

        // Studio structure
        if (!Schema::hasColumn('hosts', 'studio_structure')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->enum('studio_structure', ['solo', 'team'])->default('solo')->after('studio_types');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropIndex(['tech_support_ticket_id']);

            $table->dropColumn([
                'post_signup_step',
                'post_signup_completed_at',
                'tech_support_requested',
                'tech_support_requested_at',
                'tech_support_completed_at',
                'tech_support_ticket_id',
                'owner_phone_number',
                'owner_phone_country_code',
                'owner_phone_verified',
                'owner_phone_verified_at',
                'studio_structure',
            ]);
        });
    }
};
