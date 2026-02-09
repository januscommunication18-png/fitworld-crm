<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('category')->default('other'); // private_training, consultation, therapy, other
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedSmallInteger('buffer_minutes')->default(15); // time between bookings
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->string('location_type')->default('in_studio'); // in_studio, online, client_location
            $table->unsignedSmallInteger('max_participants')->default(1);
            $table->string('image_path')->nullable();
            $table->string('color', 7)->default('#8b5cf6'); // hex color for calendar
            $table->unsignedSmallInteger('booking_notice_hours')->default(24); // minimum advance booking
            $table->unsignedSmallInteger('cancellation_hours')->default(24); // cancellation window
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
        Schema::dropIfExists('service_plans');
    }
};
