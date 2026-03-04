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
        Schema::create('client_progress_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_progress_report_id')->constrained()->cascadeOnDelete();
            $table->enum('photo_type', ['before', 'after', 'front', 'side', 'back', 'other']);
            $table->string('file_path', 500);
            $table->string('file_name', 255)->nullable();
            $table->string('caption', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_private')->default(true); // Only visible to staff
            $table->timestamps();

            $table->index(['client_progress_report_id', 'photo_type'], 'cpp_report_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_progress_photos');
    }
};
