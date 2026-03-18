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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();

            // Event Type & Format
            $table->string('event_type')->default('in_person'); // in_person, online, hybrid
            $table->string('visibility')->default('private'); // public, unlisted, private (members only)

            // Date & Time
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('timezone')->default('America/New_York');

            // Physical Location
            $table->string('venue_name')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip_code', 20)->nullable();

            // Online Location
            $table->string('online_url')->nullable();
            $table->string('online_platform', 50)->nullable();

            // Cover Image
            $table->string('cover_image')->nullable();

            // Capacity & Registration
            $table->unsignedInteger('capacity')->nullable(); // null = unlimited
            $table->unsignedInteger('registration_count')->default(0);
            $table->unsignedInteger('waitlist_count')->default(0);

            // Settings
            $table->string('skill_level')->default('all_levels'); // beginner, intermediate, advanced, all_levels
            $table->string('audience_type')->default('all'); // adults, kids, families, seniors, all
            $table->boolean('waitlist_enabled')->default(false);
            $table->boolean('hide_attendee_list')->default(false);

            // Status & Timestamps
            $table->string('status')->default('draft'); // draft, published, cancelled, completed
            $table->timestamp('published_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['host_id', 'status']);
            $table->index(['host_id', 'start_datetime']);
            $table->index('start_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
