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
        Schema::create('price_override_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // User who requested
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null'); // Assigned approver
            $table->foreignId('actioned_by')->nullable()->constrained('users')->onDelete('set null'); // Who approved/rejected

            // Booking context (polymorphic - can be class session, service, etc.)
            $table->string('bookable_type')->nullable();
            $table->unsignedBigInteger('bookable_id')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');

            // Pricing details
            $table->decimal('original_price', 10, 2);
            $table->decimal('requested_price', 10, 2);
            $table->string('discount_code')->nullable();
            $table->text('reason')->nullable();

            // Confirmation & Security
            $table->string('confirmation_code', 20)->unique(); // e.g., PO-83921
            $table->string('status')->default('pending'); // pending, approved, rejected, expired, cancelled

            // Timestamps
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Audit trail
            $table->json('metadata')->nullable(); // Additional booking details at time of request

            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'status']);
            $table->index(['requested_by', 'status']);
            $table->index(['manager_id', 'status']);
            $table->index('confirmation_code');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_override_requests');
    }
};
