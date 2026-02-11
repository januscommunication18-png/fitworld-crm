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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('color', 7)->default('#6366f1'); // Hex color
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['host_id', 'slug']);
            $table->index('host_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
