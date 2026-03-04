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
        Schema::create('client_progress_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_progress_report_id')->constrained()->cascadeOnDelete();
            $table->string('measurement_type', 50); // weight, chest, waist, hips, biceps, thigh, etc.
            $table->decimal('value', 10, 2);
            $table->string('unit', 20)->default('cm'); // cm, in, kg, lbs
            $table->timestamps();

            $table->index(['client_progress_report_id', 'measurement_type'], 'cpm_report_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_progress_measurements');
    }
};
