<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->decimal('registration_fee', 8, 2)->nullable()->after('billing_discounts');
            $table->decimal('cancellation_fee', 8, 2)->nullable()->after('registration_fee');
            $table->integer('cancellation_grace_hours')->nullable()->default(48)->after('cancellation_fee');
        });

        Schema::table('billing_credits', function (Blueprint $table) {
            $table->decimal('registration_fee_paid', 10, 2)->default(0)->after('credit_remaining');
            $table->decimal('cancellation_fee_charged', 10, 2)->nullable()->after('registration_fee_paid');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->string('cancellation_reason', 500)->nullable()->after('cancelled_by');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('cancellation_reason');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('billing_credit_id')->nullable()->after('payment_method');
            $table->index('billing_credit_id');
        });
    }

    public function down(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->dropColumn(['registration_fee', 'cancellation_fee', 'cancellation_grace_hours']);
        });

        Schema::table('billing_credits', function (Blueprint $table) {
            $table->dropColumn(['registration_fee_paid', 'cancellation_fee_charged', 'cancelled_at', 'cancelled_by', 'cancellation_reason', 'refund_amount']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['billing_credit_id']);
            $table->dropColumn('billing_credit_id');
        });
    }
};
