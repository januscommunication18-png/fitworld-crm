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
        Schema::create('questionnaire_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('step_id')->nullable()->constrained('questionnaire_steps')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('display_style', ['plain', 'card'])->default('plain');
            $table->enum('visibility', ['public', 'internal'])->default('public');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['questionnaire_version_id', 'sort_order']);
            $table->index('step_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_blocks');
    }
};
