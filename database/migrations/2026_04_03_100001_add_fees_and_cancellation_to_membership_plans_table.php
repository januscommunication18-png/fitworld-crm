<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->decimal('registration_fee', 8, 2)->nullable()->after('billing_discounts');
            $table->decimal('cancellation_fee', 8, 2)->nullable()->after('registration_fee');
            $table->unsignedSmallInteger('cancellation_grace_hours')->nullable()->after('cancellation_fee');
        });
    }

    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn(['registration_fee', 'cancellation_fee', 'cancellation_grace_hours']);
        });
    }
};
