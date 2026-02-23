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
        Schema::create('segment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();

            // Rule grouping for AND/OR logic
            $table->integer('group_index')->default(0); // Rules in same group = AND, different groups = OR

            // Rule definition
            $table->string('field'); // e.g., 'membership_status', 'total_spent', 'last_visit_at', 'custom_field:field_key'
            $table->string('operator'); // equals, not_equals, greater_than, less_than, contains, in, not_in, is_null, is_not_null, days_ago_more_than, days_ago_less_than
            $table->text('value')->nullable(); // JSON for arrays (in/not_in), string otherwise

            // For date-based relative rules
            $table->string('relative_unit')->nullable(); // days, weeks, months
            $table->integer('relative_value')->nullable();

            $table->timestamps();

            // Index for segment lookups
            $table->index('segment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segment_rules');
    }
};