<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_passes', function (Blueprint $table) {
            $table->json('billing_discounts')->nullable()->after('new_member_prices');
            $table->decimal('registration_fee', 8, 2)->nullable()->after('billing_discounts');
            $table->decimal('cancellation_fee', 8, 2)->nullable()->after('registration_fee');
            $table->integer('cancellation_grace_hours')->nullable()->after('cancellation_fee');
        });
    }

    public function down(): void
    {
        Schema::table('class_passes', function (Blueprint $table) {
            $table->dropColumn(['billing_discounts', 'registration_fee', 'cancellation_fee', 'cancellation_grace_hours']);
        });
    }
};
