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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('type'); // unlimited, credits
            $table->decimal('price', 8, 2);
            $table->string('interval'); // monthly, yearly
            $table->unsignedSmallInteger('credits_per_cycle')->nullable();
            $table->string('eligibility_scope')->default('all_classes'); // all_classes, selected_class_plans
            $table->string('location_scope_type')->default('all'); // all, selected
            $table->json('location_ids')->nullable();
            $table->boolean('visibility_public')->default(true);
            $table->string('status')->default('draft'); // draft, active, archived
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->string('color', 7)->default('#10b981');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['host_id', 'slug']);
            $table->index(['host_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
