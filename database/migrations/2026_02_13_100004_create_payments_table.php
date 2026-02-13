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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // Optional booking reference
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();

            // Polymorphic relationship for what was paid for
            // e.g., ClassPackPurchase, CustomerMembership, ServiceSlot, etc.
            $table->nullableMorphs('payable');

            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Payment method type
            $table->enum('payment_method', [
                'stripe',      // Online card payment
                'membership',  // Used membership credits
                'pack',        // Used class pack credits
                'manual',      // Manual entry (cash, check, etc.)
                'comp'         // Complimentary/free
            ]);

            // For manual payments - specific type
            $table->enum('manual_method', [
                'cash',
                'check',
                'venmo',
                'zelle',
                'paypal',
                'cash_app',
                'bank_transfer',
                'other'
            ])->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'partially_refunded'])->default('pending');

            // Stripe fields
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();

            // Refund tracking
            $table->decimal('refunded_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Who processed this payment
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'client_id']);
            $table->index(['host_id', 'status']);
            $table->index('booking_id');
            $table->index('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
