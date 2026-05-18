<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->json('registration_fees')->nullable()->after('registration_fee');
            $table->json('cancellation_fees')->nullable()->after('cancellation_fee');
        });

        Schema::table('service_plans', function (Blueprint $table) {
            $table->json('registration_fees')->nullable()->after('registration_fee');
            $table->json('cancellation_fees')->nullable()->after('cancellation_fee');
        });

        Schema::table('class_passes', function (Blueprint $table) {
            $table->json('registration_fees')->nullable()->after('registration_fee');
            $table->json('cancellation_fees')->nullable()->after('cancellation_fee');
        });
    }

    public function down(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->dropColumn(['registration_fees', 'cancellation_fees']);
        });

        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropColumn(['registration_fees', 'cancellation_fees']);
        });

        Schema::table('class_passes', function (Blueprint $table) {
            $table->dropColumn(['registration_fees', 'cancellation_fees']);
        });
    }
};
