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
        // Pivot table for static segment membership
        Schema::create('client_segment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();

            // For static segments: who added and when
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();

            // For dynamic segments: when auto-matched
            $table->timestamp('matched_at')->nullable();

            $table->timestamps();

            // Unique constraint
            $table->unique(['client_id', 'segment_id']);

            // Indexes
            $table->index('segment_id');
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_segment');
    }
};
