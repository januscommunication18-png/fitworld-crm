<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('category')->default('other'); // yoga, pilates, fitness, wellness, other
            $table->string('type')->default('group'); // group, workshop, special_event
            $table->unsignedSmallInteger('default_duration_minutes')->default(60);
            $table->unsignedSmallInteger('default_capacity')->default(20);
            $table->unsignedSmallInteger('min_capacity')->default(1);
            $table->decimal('default_price', 8, 2)->nullable();
            $table->decimal('drop_in_price', 8, 2)->nullable();
            $table->string('color', 7)->default('#6366f1'); // hex color for calendar
            $table->string('difficulty_level')->default('all_levels'); // beginner, intermediate, advanced, all_levels
            $table->json('equipment_needed')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible_on_booking_page')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['host_id', 'slug']);
            $table->index(['host_id', 'is_active']);
            $table->index(['host_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_plans');
    }
};
