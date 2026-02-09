<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plan_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_id')->constrained('service_plans')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->decimal('custom_price', 8, 2)->nullable(); // overrides service_plan.price
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_plan_id', 'instructor_id']);
            $table->index(['instructor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_instructors');
    }
};
