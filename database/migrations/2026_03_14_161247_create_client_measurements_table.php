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
        Schema::create('client_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('measured_at');
            $table->decimal('weight', 6, 2)->nullable();
            $table->string('weight_unit', 10)->default('kg');
            $table->decimal('body_fat', 5, 2)->nullable();
            $table->decimal('chest', 6, 2)->nullable();
            $table->decimal('waist', 6, 2)->nullable();
            $table->decimal('hips', 6, 2)->nullable();
            $table->decimal('biceps_left', 6, 2)->nullable();
            $table->decimal('biceps_right', 6, 2)->nullable();
            $table->decimal('thigh_left', 6, 2)->nullable();
            $table->decimal('thigh_right', 6, 2)->nullable();
            $table->decimal('calf_left', 6, 2)->nullable();
            $table->decimal('calf_right', 6, 2)->nullable();
            $table->decimal('neck', 6, 2)->nullable();
            $table->decimal('shoulders', 6, 2)->nullable();
            $table->string('measurement_unit', 10)->default('cm');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'measured_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_measurements');
    }
};
