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
        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('membership_plan_id')->constrained('membership_plans')->cascadeOnDelete();

            // Stripe subscription (nullable for manual memberships)
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_customer_id')->nullable();

            // Status
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->enum('payment_method', ['stripe', 'manual'])->default('manual');

            // Credits (for credit-based memberships)
            $table->unsignedSmallInteger('credits_remaining')->nullable();
            $table->unsignedSmallInteger('credits_per_period')->nullable();

            // Period tracking
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();

            // Lifecycle dates
            $table->timestamp('started_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Who sold/created this membership
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'client_id']);
            $table->index(['host_id', 'status']);
            $table->index('stripe_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_memberships');
    }
};
