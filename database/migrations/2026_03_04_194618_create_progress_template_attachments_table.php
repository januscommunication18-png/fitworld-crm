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
        Schema::create('progress_template_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('progress_template_id')->constrained()->restrictOnDelete();
            $table->boolean('is_required')->default(false);
            $table->enum('trigger_point', ['before_class', 'after_class', 'any'])->default('after_class');
            $table->boolean('notify_instructor')->default(true);
            $table->timestamps();

            $table->unique(['host_id', 'class_plan_id', 'progress_template_id'], 'pta_host_plan_template_unique');
            $table->index('class_plan_id', 'pta_class_plan_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_template_attachments');
    }
};
