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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->onDelete('cascade');

            // Basic Info
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->json('address')->nullable(); // {street, city, state, zip, country}

            // Lifecycle Status
            $table->enum('status', ['lead', 'client', 'member', 'at_risk'])->default('client');
            $table->enum('membership_status', ['none', 'active', 'paused', 'cancelled'])->default('none');

            // Lead Source Tracking
            $table->enum('lead_source', ['manual', 'marketing', 'website', 'lead_magnet', 'fitnearyou', 'referral'])->default('manual');
            $table->string('source_url')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referral_id')->nullable();

            // Activity Tracking
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamp('next_booking_at')->nullable();
            $table->timestamp('converted_at')->nullable(); // When lead became client/member

            // Membership Link (FK added later when memberships table exists)
            $table->unsignedBigInteger('membership_id')->nullable();
            $table->timestamp('membership_expires_at')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Soft Delete
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'status']);
            $table->index(['host_id', 'email']);
            $table->index(['host_id', 'membership_status']);
            $table->index(['host_id', 'lead_source']);
            $table->index(['host_id', 'last_visit_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
