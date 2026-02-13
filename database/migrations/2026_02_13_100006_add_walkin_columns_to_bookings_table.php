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
        Schema::table('bookings', function (Blueprint $table) {
            // Booking source tracking
            $table->enum('booking_source', ['online', 'internal_walkin', 'api'])->default('online')->after('status');

            // Intake/questionnaire status
            $table->enum('intake_status', ['not_required', 'pending', 'completed', 'waived'])->default('not_required')->after('booking_source');
            $table->foreignId('intake_waived_by')->nullable()->after('intake_status')->constrained('users')->nullOnDelete();
            $table->string('intake_waived_reason')->nullable()->after('intake_waived_by');

            // Capacity override tracking (for walk-ins when class is full)
            $table->boolean('capacity_override')->default(false)->after('intake_waived_reason');
            $table->string('capacity_override_reason')->nullable()->after('capacity_override');

            // Who created this booking (staff member for walk-ins)
            $table->foreignId('created_by_user_id')->nullable()->after('capacity_override_reason')->constrained('users')->nullOnDelete();

            // Customer membership reference (proper FK now that table exists)
            $table->foreignId('customer_membership_id')->nullable()->after('membership_id')->constrained('customer_memberships')->nullOnDelete();

            // Class pack purchase reference
            $table->foreignId('class_pack_purchase_id')->nullable()->after('customer_membership_id')->constrained('class_pack_purchases')->nullOnDelete();

            // Indexes
            $table->index('booking_source');
            $table->index('intake_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['intake_waived_by']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['customer_membership_id']);
            $table->dropForeign(['class_pack_purchase_id']);

            $table->dropIndex(['booking_source']);
            $table->dropIndex(['intake_status']);

            $table->dropColumn([
                'booking_source',
                'intake_status',
                'intake_waived_by',
                'intake_waived_reason',
                'capacity_override',
                'capacity_override_reason',
                'created_by_user_id',
                'customer_membership_id',
                'class_pack_purchase_id',
            ]);
        });
    }
};
