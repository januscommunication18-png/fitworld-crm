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
        Schema::create('client_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('client_field_sections')->nullOnDelete();

            $table->string('field_key'); // Unique slug identifier
            $table->string('field_label');
            $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'dropdown', 'checkbox', 'yes_no']);
            $table->json('options')->nullable(); // For dropdown/checkbox options
            $table->boolean('is_required')->default(false);
            $table->string('help_text')->nullable();
            $table->string('default_value')->nullable();
            $table->boolean('show_on_add')->default(true);
            $table->boolean('show_on_edit')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System fields can't be deleted

            $table->timestamps();

            $table->unique(['host_id', 'field_key']);
            $table->index(['host_id', 'section_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_field_definitions');
    }
};
