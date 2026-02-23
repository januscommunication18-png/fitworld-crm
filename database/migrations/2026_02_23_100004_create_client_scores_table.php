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
        Schema::create('client_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // Current engagement score (0-1000)
            $table->integer('engagement_score')->default(0);

            // Loyalty tier derived from score
            $table->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'vip'])->default('bronze');

            // Component scores for transparency
            $table->integer('attendance_score')->default(0);
            $table->integer('spending_score')->default(0);
            $table->integer('engagement_score_component')->default(0);
            $table->integer('loyalty_score')->default(0);

            // Activity metrics (for scoring calculation)
            $table->integer('total_classes_30d')->default(0);
            $table->integer('total_no_shows_30d')->default(0);
            $table->integer('total_late_cancels_30d')->default(0);
            $table->integer('total_referrals')->default(0);
            $table->integer('membership_renewals')->default(0);
            $table->integer('days_since_last_visit')->nullable();

            // Score history
            $table->integer('previous_score')->default(0);
            $table->timestamp('score_calculated_at')->nullable();

            $table->timestamps();

            // Unique constraint - one score record per client
            $table->unique(['host_id', 'client_id']);

            // Indexes
            $table->index(['host_id', 'engagement_score']);
            $table->index(['host_id', 'loyalty_tier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_scores');
    }
};
