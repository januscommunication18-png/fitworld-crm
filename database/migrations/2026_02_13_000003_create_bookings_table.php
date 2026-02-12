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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->morphs('bookable'); // bookable_type, bookable_id (ClassSession, ServiceSlot)
            $table->string('status')->default('confirmed'); // confirmed, cancelled, no_show, completed
            $table->string('payment_method')->nullable(); // stripe, membership, pack, manual, cash
            $table->unsignedBigInteger('membership_id')->nullable(); // FK to future customer_memberships
            $table->decimal('price_paid', 8, 2)->nullable();
            $table->unsignedSmallInteger('credits_used')->nullable();
            $table->timestamp('booked_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->index(['host_id', 'client_id']);
            $table->index(['host_id', 'status']);
            // Note: morphs() already creates an index for bookable_type, bookable_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
