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
        Schema::create('one_on_one_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');

            // Guest information
            $table->string('guest_first_name', 100);
            $table->string('guest_last_name', 100);
            $table->string('guest_email', 255);
            $table->string('guest_phone', 50)->nullable();
            $table->text('guest_notes')->nullable();

            // Meeting details
            $table->string('meeting_type', 20); // in_person, phone, video
            $table->unsignedInteger('duration_minutes');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('timezone', 100);

            // Status
            $table->string('status', 20)->default('confirmed'); // confirmed, cancelled, completed, no_show
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by', 20)->nullable(); // guest, host
            $table->text('cancellation_reason')->nullable();

            // Reschedule tracking
            $table->foreignId('rescheduled_from_id')->nullable()->constrained('one_on_one_bookings')->onDelete('set null');
            $table->unsignedInteger('reschedule_count')->default(0);

            // Tokens for guest actions
            $table->string('confirmation_token', 64);
            $table->string('manage_token', 64);

            // Tracking
            $table->timestamp('booked_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['booking_profile_id', 'start_time']);
            $table->index(['host_id', 'status']);
            $table->index('guest_email');
            $table->index('confirmation_token');
            $table->index('manage_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_on_one_bookings');
    }
};
