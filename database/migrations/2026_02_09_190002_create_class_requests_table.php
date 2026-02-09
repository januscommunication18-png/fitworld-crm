<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('class_plan_id')->nullable()->constrained('class_plans')->nullOnDelete();
            $table->foreignId('service_plan_id')->nullable()->constrained('service_plans')->nullOnDelete();
            $table->string('requester_name');
            $table->string('requester_email');
            $table->json('preferred_days')->nullable(); // JSON array of day names
            $table->json('preferred_times')->nullable(); // JSON array of time ranges
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, scheduled, ignored
            $table->foreignId('scheduled_session_id')->nullable()->constrained('class_sessions')->nullOnDelete();
            $table->timestamps();

            $table->index(['host_id', 'status']);
            $table->index('class_plan_id');
            $table->index('service_plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_requests');
    }
};
