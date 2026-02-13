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
        Schema::create('questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questionnaire_questions')->cascadeOnDelete();
            $table->text('answer')->nullable();
            $table->timestamps();

            $table->unique(['questionnaire_response_id', 'question_id'], 'unique_response_question');
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_answers');
    }
};
