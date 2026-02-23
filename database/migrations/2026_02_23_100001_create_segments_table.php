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
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();

            // Basic info
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6366f1'); // Hex color for UI

            // Segment type: static (manual), dynamic (rule-based), smart (score-based)
            $table->enum('type', ['static', 'dynamic', 'smart'])->default('dynamic');

            // For smart segments - tier thresholds
            $table->string('tier')->nullable(); // bronze, silver, gold, vip
            $table->integer('min_score')->nullable();
            $table->integer('max_score')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System segments can't be deleted

            // Cache for performance
            $table->integer('member_count')->default(0);
            $table->timestamp('member_count_updated_at')->nullable();

            // Analytics cache
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('avg_visit_frequency', 8, 2)->default(0);

            $table->timestamps();

            // Indexes
            $table->unique(['host_id', 'slug']);
            $table->index(['host_id', 'type']);
            $table->index(['host_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};