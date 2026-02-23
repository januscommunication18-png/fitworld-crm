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
        // Configurable scoring rules per host
        Schema::create('scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();

            // Event type this rule applies to
            $table->string('event_type'); // class_attended, no_show, late_cancel, referral, membership_renewal, purchase, inactivity_30d

            // Points to award (can be negative)
            $table->integer('points');

            // Description for admin UI
            $table->string('description');

            // Status
            $table->boolean('is_active')->default(true);

            // Order for display
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Unique event type per host
            $table->unique(['host_id', 'event_type']);
        });

        // Tier thresholds per host
        Schema::create('scoring_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();

            $table->string('tier'); // bronze, silver, gold, vip
            $table->string('display_name'); // Custom name like "Starter", "Regular", "VIP"
            $table->integer('min_score');
            $table->integer('max_score')->nullable(); // null for highest tier

            $table->string('color', 7)->default('#6366f1');
            $table->string('icon')->nullable(); // Tabler icon name
            $table->text('benefits')->nullable(); // Description of tier benefits

            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Unique tier per host
            $table->unique(['host_id', 'tier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_tiers');
        Schema::dropIfExists('scoring_rules');
    }
};
