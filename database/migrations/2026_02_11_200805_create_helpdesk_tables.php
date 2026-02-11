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
        // Helpdesk Tags
        Schema::create('helpdesk_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->timestamps();

            $table->unique(['host_id', 'name']);
        });

        // Helpdesk Tickets
        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('source_type', ['booking_request', 'general_inquiry', 'lead_magnet', 'manual'])->default('manual');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('service_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time')->nullable();
            $table->enum('status', ['open', 'in_progress', 'customer_reply', 'resolved'])->default('open');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_url')->nullable();
            $table->json('utm_params')->nullable();
            $table->timestamps();

            $table->index(['host_id', 'status']);
            $table->index(['host_id', 'created_at']);
        });

        // Helpdesk Messages
        Schema::create('helpdesk_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('sender_type', ['staff', 'customer', 'system'])->default('staff');
            $table->text('message');
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        // Helpdesk Ticket Tag Pivot
        Schema::create('helpdesk_ticket_tag', function (Blueprint $table) {
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('helpdesk_tags')->cascadeOnDelete();
            $table->primary(['ticket_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_tag');
        Schema::dropIfExists('helpdesk_messages');
        Schema::dropIfExists('helpdesk_tickets');
        Schema::dropIfExists('helpdesk_tags');
    }
};
