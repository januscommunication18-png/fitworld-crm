<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('class_plan_id')->constrained('class_plans')->cascadeOnDelete();
            $table->foreignId('primary_instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('backup_instructor_id')->nullable()->constrained('instructors')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('title')->nullable(); // defaults to class_plan name
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedInteger('duration_minutes'); // overrides plan default
            $table->unsignedInteger('capacity'); // overrides plan default
            $table->decimal('price', 8, 2)->nullable(); // overrides plan default
            $table->string('status')->default('draft'); // draft, published, cancelled
            $table->string('recurrence_rule')->nullable(); // RRULE format
            $table->foreignId('recurrence_parent_id')->nullable()->constrained('class_sessions')->nullOnDelete();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable(); // internal notes
            $table->timestamps();

            $table->index(['host_id', 'status']);
            $table->index(['primary_instructor_id', 'start_time']);
            $table->index(['location_id', 'room_id', 'start_time']);
            $table->index('class_plan_id');
            $table->index('start_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
