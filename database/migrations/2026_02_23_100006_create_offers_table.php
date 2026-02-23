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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();

            // Basic Details
            $table->string('name');
            $table->string('code')->nullable(); // Promo code (optional)
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable(); // For marketing
            $table->text('internal_notes')->nullable(); // Staff only

            // Status
            $table->enum('status', ['draft', 'active', 'paused', 'expired', 'archived'])->default('draft');

            // Duration
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable(); // Optional time-based
            $table->time('end_time')->nullable();
            $table->boolean('auto_expire')->default(true);

            // Recurring Offer (e.g., Every January)
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern')->nullable(); // monthly, yearly
            $table->json('recurring_months')->nullable(); // [1, 6, 12] for Jan, Jun, Dec

            // Applicability - What can this offer be used for?
            $table->enum('applies_to', ['all', 'classes', 'services', 'memberships', 'retail', 'class_packs', 'specific'])->default('all');
            $table->json('applicable_item_ids')->nullable(); // Specific item IDs when applies_to = 'specific'

            // Plan Applicability
            $table->enum('plan_scope', ['all_plans', 'specific_plans', 'first_time', 'trial', 'upgrade'])->default('all_plans');
            $table->json('applicable_plan_ids')->nullable(); // Specific membership plan IDs

            // Discount Type
            $table->enum('discount_type', [
                'percentage',
                'fixed_amount',
                'buy_x_get_y',
                'free_class',
                'free_addon',
                'bundle'
            ])->default('percentage');

            // Discount Values
            $table->decimal('discount_value', 10, 2)->nullable(); // For percentage or fixed amount
            $table->integer('buy_quantity')->nullable(); // For buy X get Y
            $table->integer('get_quantity')->nullable(); // For buy X get Y
            $table->integer('free_classes')->nullable(); // For free class credits
            $table->json('free_addon_ids')->nullable(); // For free add-ons

            // Multi-Currency Support
            $table->json('discount_amounts')->nullable(); // {"USD": 10, "CAD": 12, "GBP": 8} for fixed amounts

            // Target Audience (Segmentation Link)
            $table->enum('target_audience', [
                'all_members',
                'specific_segment',
                'new_members',
                'inactive_members',
                'high_spenders',
                'vip_tier'
            ])->default('all_members');
            $table->foreignId('segment_id')->nullable()->constrained()->nullOnDelete();

            // Usage Control
            $table->integer('total_usage_limit')->nullable(); // Max redemptions total
            $table->integer('per_member_limit')->nullable(); // Max per client
            $table->integer('first_x_users')->nullable(); // First X users only
            $table->boolean('auto_stop_on_limit')->default(true);

            // Current usage tracking
            $table->integer('total_redemptions')->default(0);
            $table->decimal('total_discount_given', 12, 2)->default(0);
            $table->decimal('total_revenue_generated', 12, 2)->default(0);

            // Channel Control
            $table->boolean('online_booking_only')->default(false);
            $table->boolean('front_desk_only')->default(false);
            $table->boolean('app_only')->default(false);
            $table->boolean('manual_override_allowed')->default(true);

            // Stack Rules
            $table->boolean('can_combine')->default(false); // Can combine with other offers?
            $table->boolean('highest_discount_wins')->default(true);

            // Auto Apply vs Coupon
            $table->boolean('auto_apply')->default(false); // Auto apply if eligible
            $table->boolean('require_code')->default(false); // Require coupon code

            // Invoice Display
            $table->boolean('show_on_invoice')->default(true);
            $table->string('invoice_line_text')->nullable(); // Custom text for invoice

            // Analytics
            $table->integer('new_members_acquired')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0); // Percentage

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['host_id', 'code']);
            $table->index(['host_id', 'status']);
            $table->index(['host_id', 'start_date', 'end_date']);
            $table->index(['host_id', 'target_audience']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
