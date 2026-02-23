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
        Schema::create('offer_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // What was purchased with this offer
            $table->nullableMorphs('redeemable'); // ClassPack, MembershipPlan, Booking, etc.

            // Discount details
            $table->decimal('original_price', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('final_price', 10, 2);
            $table->string('currency', 3)->default('USD');

            // How it was applied
            $table->enum('channel', ['online', 'front_desk', 'app', 'manual'])->default('online');
            $table->string('promo_code_used')->nullable(); // The actual code entered

            // Reference to payment/invoice
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            // Status
            $table->enum('status', ['applied', 'completed', 'refunded', 'voided'])->default('applied');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Applied by (staff or system)
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'offer_id']);
            $table->index(['host_id', 'client_id']);
            $table->index(['host_id', 'created_at']);
            $table->index(['offer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_redemptions');
    }
};
