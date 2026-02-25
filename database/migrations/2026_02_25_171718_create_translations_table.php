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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->onDelete('cascade');

            // Category: field_labels, page_titles, general_content, buttons, messages
            $table->string('category', 50);

            // Unique key identifier (e.g., 'booking.confirm_button', 'page.dashboard_title')
            $table->string('translation_key');

            // The English value (stored as reference/default)
            $table->text('value_en');

            // Translated values for each supported language
            $table->text('value_fr')->nullable(); // French
            $table->text('value_de')->nullable(); // German
            $table->text('value_es')->nullable(); // Spanish

            // Page/context this translation belongs to (optional, for filtering)
            $table->string('page_context', 100)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: one translation per key per host
            $table->unique(['host_id', 'translation_key']);

            // Indexes for faster lookups
            $table->index(['host_id', 'category']);
            $table->index(['host_id', 'page_context']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
