<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            // PayPal Business (Own) — the studio's own PayPal REST app credentials.
            $table->string('paypal_environment')->nullable()->after('square_location_id');   // sandbox | live
            $table->string('paypal_client_id')->nullable()->after('paypal_environment');
            $table->text('paypal_client_secret')->nullable()->after('paypal_client_id');      // encrypted at rest
            $table->string('paypal_webhook_id')->nullable()->after('paypal_client_secret');
        });
    }

    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_environment',
                'paypal_client_id',
                'paypal_client_secret',
                'paypal_webhook_id',
            ]);
        });
    }
};
