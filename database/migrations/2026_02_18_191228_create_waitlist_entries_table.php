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
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('class_request_id')->nullable()->constrained('class_requests')->nullOnDelete();
            $table->foreignId('class_plan_id')->nullable()->constrained('class_plans')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('waiting'); // waiting, offered, claimed, expired, cancelled
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->index(['host_id', 'status']);
            $table->index('class_plan_id');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
    }
};
