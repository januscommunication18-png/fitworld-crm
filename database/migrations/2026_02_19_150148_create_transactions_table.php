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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();

            // Unique transaction identifier (TXN_ULID format)
            $table->string('transaction_id', 32)->unique();

            // Transaction type
            $table->enum('type', [
                'class_booking',
                'service_booking',
                'membership_purchase',
                'class_pack_purchase',
            ]);

            // Polymorphic relationship for what was purchased
            // e.g., ClassSession, ServiceSlot, MembershipPlan, ClassPack
            $table->nullableMorphs('purchasable');

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Status
            $table->enum('status', [
                'pending',
                'authorized',
                'paid',
                'failed',
                'refunded',
                'partially_refunded',
                'cancelled',
            ])->default('pending');

            // Payment method
            $table->enum('payment_method', [
                'stripe',
                'manual',
                'membership',
                'pack',
                'comp',
            ])->nullable();

            // For manual payments - specific type
            $table->enum('manual_method', [
                'cash',
                'check',
                'venmo',
                'zelle',
                'paypal',
                'cash_app',
                'bank_transfer',
                'other',
            ])->nullable();

            // Stripe fields
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_charge_id')->nullable();

            // Refund tracking
            $table->decimal('refunded_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Metadata for additional info (booking details, promo codes, etc.)
            $table->json('metadata')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'client_id']);
            $table->index(['host_id', 'status']);
            $table->index(['host_id', 'type']);
            $table->index('stripe_payment_intent_id');
            $table->index('stripe_checkout_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
