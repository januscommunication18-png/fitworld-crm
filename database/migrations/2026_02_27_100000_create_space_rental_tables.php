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
        // 1. Space Rental Configurations (Defines rentable spaces)
        Schema::create('space_rental_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->enum('rentable_type', ['location', 'room']);
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('hourly_rates')->nullable(); // Multi-currency: {"USD": 75.00, "EUR": 65.00}
            $table->integer('minimum_hours')->default(2);
            $table->integer('maximum_hours')->nullable();
            $table->json('deposit_rates')->nullable(); // Multi-currency
            $table->json('allowed_purposes')->nullable(); // ['photo_shoot', 'video_production', 'workshop', 'training', 'other']
            $table->json('amenities_included')->nullable();
            $table->text('rules')->nullable();
            $table->integer('setup_time_minutes')->default(0);
            $table->integer('cleanup_time_minutes')->default(15);
            $table->boolean('requires_waiver')->default(true);
            $table->string('waiver_document_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['host_id', 'is_active']);
            $table->index(['host_id', 'rentable_type']);
        });

        // 2. Space Rentals (Actual bookings)
        Schema::create('space_rentals', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('space_rental_config_id')->constrained()->cascadeOnDelete();

            // Client info - either registered or external
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_client_name')->nullable();
            $table->string('external_client_email')->nullable();
            $table->string('external_client_phone')->nullable();
            $table->string('external_client_company')->nullable();

            // Purpose and timing
            $table->enum('purpose', ['photo_shoot', 'video_production', 'workshop', 'training', 'other']);
            $table->text('purpose_notes')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            // Pricing
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('hours_booked', 4, 1);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');

            // Status
            $table->enum('status', ['draft', 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('draft');

            // Waiver tracking
            $table->boolean('waiver_signed')->default(false);
            $table->timestamp('waiver_signed_at')->nullable();
            $table->string('waiver_signer_name')->nullable();
            $table->string('waiver_signer_ip')->nullable();

            // Deposit tracking
            $table->enum('deposit_status', ['not_required', 'pending', 'paid', 'partially_refunded', 'refunded', 'forfeited'])->default('not_required');
            $table->timestamp('deposit_paid_at')->nullable();
            $table->decimal('deposit_refund_amount', 10, 2)->nullable();
            $table->text('deposit_refund_reason')->nullable();
            $table->timestamp('deposit_refunded_at')->nullable();

            // Damage tracking
            $table->boolean('damage_reported')->default(false);
            $table->text('damage_notes')->nullable();
            $table->decimal('damage_charge', 10, 2)->default(0);

            // Internal notes
            $table->text('internal_notes')->nullable();

            // Audit fields
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['host_id', 'status']);
            $table->index(['host_id', 'start_time', 'end_time']);
            $table->index(['space_rental_config_id', 'start_time']);
        });

        // 3. Space Rental Status Logs (Audit trail)
        Schema::create('space_rental_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_rental_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['space_rental_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_rental_status_logs');
        Schema::dropIfExists('space_rentals');
        Schema::dropIfExists('space_rental_configs');
    }
};
