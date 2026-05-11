<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_plan_instructor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['class_plan_id', 'instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_plan_instructor');
    }
};
