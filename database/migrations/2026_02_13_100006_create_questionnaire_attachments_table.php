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
        Schema::create('questionnaire_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->string('attachable_type')->comment('App\\Models\\ClassPlan, ServicePlan, MembershipPlan');
            $table->unsignedBigInteger('attachable_id');
            $table->boolean('is_required')->default(true);
            $table->enum('collection_timing', [
                'before_booking',
                'after_booking',
                'before_first_session',
            ])->default('before_first_session');
            $table->enum('applies_to', [
                'first_time_only',
                'every_booking',
            ])->default('first_time_only');
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
            $table->index('questionnaire_id');
            $table->unique(['questionnaire_id', 'attachable_type', 'attachable_id'], 'unique_questionnaire_attachment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_attachments');
    }
};
