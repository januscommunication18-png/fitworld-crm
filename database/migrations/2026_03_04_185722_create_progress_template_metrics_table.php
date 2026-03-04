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
        Schema::create('progress_template_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_template_section_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('metric_key', 100);
            $table->text('description')->nullable();
            $table->enum('metric_type', ['slider', 'number', 'select', 'checkbox_list', 'rating', 'text']);
            $table->string('unit', 50)->nullable(); // kg, cm, minutes, etc.
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->decimal('step', 10, 2)->default(1);
            $table->json('options')->nullable(); // For select/checkbox: ["Beginner", "Intermediate", "Advanced"]
            $table->json('rating_labels')->nullable(); // ["Poor", "Fair", "Good", "Excellent"]
            $table->boolean('is_required')->default(false);
            $table->decimal('weight', 5, 2)->default(1.00); // For scoring
            $table->string('chart_color', 10)->nullable(); // Hex color for graphs
            $table->boolean('show_on_summary')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['progress_template_section_id', 'sort_order'], 'pt_metrics_section_sort_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_template_metrics');
    }
};
