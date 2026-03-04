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
        Schema::create('client_progress_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_progress_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('progress_template_metric_id')->constrained()->restrictOnDelete();
            $table->decimal('value_numeric', 10, 2)->nullable(); // For slider/number/rating
            $table->text('value_text')->nullable(); // For text
            $table->json('value_json')->nullable(); // For checkbox_list, select
            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['client_progress_report_id', 'progress_template_metric_id'], 'cpv_report_metric_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_progress_values');
    }
};
