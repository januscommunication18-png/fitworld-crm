<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('service_plan_id')->constrained('service_plans')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('available'); // available, booked, blocked
            $table->decimal('price', 8, 2)->nullable(); // overrides service_plan.price
            $table->text('notes')->nullable(); // internal notes
            $table->string('recurrence_rule')->nullable(); // for recurring slots (RRULE format)
            $table->foreignId('recurrence_parent_id')->nullable()->constrained('service_slots')->nullOnDelete();
            $table->timestamps();

            $table->index(['host_id', 'status']);
            $table->index(['instructor_id', 'start_time']);
            $table->index(['service_plan_id', 'status']);
            $table->index('start_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_slots');
    }
};
