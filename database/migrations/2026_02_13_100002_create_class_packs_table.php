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
        Schema::create('class_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();

            // Pack details
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('class_count'); // Number of classes in pack
            $table->decimal('price', 8, 2);

            // Expiration
            $table->unsignedSmallInteger('expires_after_days')->nullable(); // e.g., 30, 60, 90 days

            // Eligibility - which class plans can use this pack
            $table->json('eligible_class_plan_ids')->nullable(); // null = all classes

            // Stripe integration
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();

            // Status
            $table->enum('status', ['active', 'archived'])->default('active');

            // Visibility
            $table->boolean('visibility_public')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_packs');
    }
};
