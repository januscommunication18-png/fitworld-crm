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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Action performed
            $table->string('action'); // e.g., 'booking.created', 'booking.capacity_overridden', 'payment.processed'

            // Polymorphic relationship to the audited entity
            $table->nullableMorphs('auditable');

            // Data snapshots
            $table->json('before_data')->nullable(); // State before change
            $table->json('after_data')->nullable();  // State after change
            $table->json('context')->nullable();     // Additional context (e.g., session info, request data)

            // Required reason for certain actions
            $table->string('reason')->nullable();

            // Request metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Indexes (note: nullableMorphs already creates auditable_type_auditable_id index)
            $table->index(['host_id', 'action']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
