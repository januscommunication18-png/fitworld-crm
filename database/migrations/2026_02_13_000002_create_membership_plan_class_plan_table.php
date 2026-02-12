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
        Schema::create('membership_plan_class_plan', function (Blueprint $table) {
            $table->foreignId('membership_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_plan_id')->constrained()->cascadeOnDelete();
            $table->primary(['membership_plan_id', 'class_plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plan_class_plan');
    }
};
