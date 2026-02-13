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
        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_block_id')->constrained()->cascadeOnDelete();
            $table->string('question_key')->comment('Unique slug identifier within questionnaire');
            $table->text('question_label');
            $table->enum('question_type', [
                'short_text',
                'long_text',
                'email',
                'phone',
                'yes_no',
                'single_select',
                'multi_select',
                'dropdown',
                'date',
                'number',
                'acknowledgement',
            ]);
            $table->json('options')->nullable()->comment('For select/checkbox types: [{key, label}]');
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->json('validation_rules')->nullable()->comment('{ min, max, pattern, etc. }');
            $table->enum('visibility', ['client', 'instructor_only'])->default('client');
            $table->boolean('is_sensitive')->default(false)->comment('Restricted visibility for health data');
            $table->json('tags')->nullable()->comment('For reporting: injury, goals, experience');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['questionnaire_block_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_questions');
    }
};
