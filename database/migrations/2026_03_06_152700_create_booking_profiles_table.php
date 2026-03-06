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
        Schema::create('booking_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained()->onDelete('cascade');

            // Status
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_setup_complete')->default(false);

            // Profile info (can override instructor defaults)
            $table->string('display_name')->nullable();
            $table->string('title')->nullable();
            $table->text('bio')->nullable();

            // Meeting configuration
            $table->json('meeting_types')->nullable(); // in_person, phone, video - defaults set in model
            $table->string('video_link', 500)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->text('in_person_location')->nullable();

            // Slot durations
            $table->json('allowed_durations')->nullable(); // minutes - defaults set in model
            $table->unsignedInteger('default_duration')->default(30);

            // Buffer & limits
            $table->unsignedInteger('buffer_before')->default(0); // minutes
            $table->unsignedInteger('buffer_after')->default(0); // minutes
            $table->unsignedInteger('daily_max_meetings')->nullable();

            // Booking window
            $table->unsignedInteger('min_notice_hours')->default(24);
            $table->unsignedInteger('max_advance_days')->default(60);

            // Working schedule
            $table->json('working_days')->nullable(); // 0=Sun, 1=Mon, etc. - defaults set in model
            $table->json('availability_by_day')->nullable(); // Per-day time overrides
            $table->time('default_start_time')->default('09:00:00');
            $table->time('default_end_time')->default('17:00:00');

            // Reschedule/cancel settings
            $table->boolean('allow_reschedule')->default(true);
            $table->unsignedInteger('reschedule_cutoff_hours')->default(24);
            $table->boolean('allow_cancel')->default(true);
            $table->unsignedInteger('cancel_cutoff_hours')->default(24);

            // Tracking
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['host_id', 'instructor_id']);
            $table->index(['host_id', 'is_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_profiles');
    }
};
