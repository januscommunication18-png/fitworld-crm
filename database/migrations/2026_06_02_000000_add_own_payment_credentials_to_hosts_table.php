<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            // Stripe (Own) — the studio's own Stripe account keys.
            $table->text('stripe_own_publishable_key')->nullable()->after('stripe_account_id');
            $table->text('stripe_own_secret_key')->nullable()->after('stripe_own_publishable_key');       // encrypted at rest
            $table->text('stripe_own_webhook_secret')->nullable()->after('stripe_own_secret_key');          // encrypted at rest

            // Square.
            $table->string('square_environment')->nullable()->after('stripe_own_webhook_secret');           // sandbox | production
            $table->string('square_application_id')->nullable()->after('square_environment');
            $table->text('square_access_token')->nullable()->after('square_application_id');                 // encrypted at rest
            $table->string('square_location_id')->nullable()->after('square_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_own_publishable_key',
                'stripe_own_secret_key',
                'stripe_own_webhook_secret',
                'square_environment',
                'square_application_id',
                'square_access_token',
                'square_location_id',
            ]);
        });
    }
};
