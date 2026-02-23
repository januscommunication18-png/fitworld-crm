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
        // Log individual scoring events for audit trail
        Schema::create('score_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // Event type
            $table->string('event_type'); // class_attended, no_show, late_cancel, referral, membership_renewal, purchase, inactivity_penalty

            // Points awarded (positive or negative)
            $table->integer('points');

            // Score before and after
            $table->integer('score_before');
            $table->integer('score_after');

            // Reference to source event (polymorphic)
            $table->nullableMorphs('source');

            // Description
            $table->string('description')->nullable();

            $table->timestamp('created_at');

            // Indexes
            $table->index(['host_id', 'client_id']);
            $table->index(['host_id', 'event_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_events');
    }
};
