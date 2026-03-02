<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration transforms the basic ClassPack system into a comprehensive ClassPass system
     * with advanced features for eligibility rules, variable credit consumption, expiration management,
     * sharing/transfer, auto-renewal, and rollover.
     */
    public function up(): void
    {
        // Step 1: Drop foreign key constraints before renaming
        Schema::table('class_pack_purchases', function (Blueprint $table) {
            $table->dropForeign(['class_pack_id']);
        });

        // Also drop FK from bookings if it exists
        if (Schema::hasColumn('bookings', 'class_pack_purchase_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                // Try to drop the foreign key - may fail if doesn't exist
                try {
                    $table->dropForeign(['class_pack_purchase_id']);
                } catch (\Exception $e) {
                    // Foreign key may not exist, continue
                }
            });
        }

        // Step 2: Rename class_packs table to class_passes
        Schema::rename('class_packs', 'class_passes');

        // Step 3: Add new columns to class_passes
        Schema::table('class_passes', function (Blueprint $table) {
            // Activation Rule
            $table->enum('activation_type', ['on_purchase', 'on_first_booking'])
                  ->default('on_purchase')
                  ->after('expires_after_days');

            // Class Eligibility (Enhanced)
            $table->enum('eligibility_type', ['all', 'categories', 'class_plans', 'instructors', 'locations'])
                  ->default('all')
                  ->after('activation_type');
            $table->json('eligible_categories')->nullable()->after('eligibility_type');
            $table->json('eligible_instructor_ids')->nullable()->after('eligible_categories');
            $table->json('eligible_location_ids')->nullable()->after('eligible_instructor_ids');
            $table->json('excluded_class_types')->nullable()->after('eligible_location_ids');

            // Credit Consumption Rules
            $table->unsignedTinyInteger('default_credits_per_class')->default(1)->after('excluded_class_types');
            $table->json('credit_rules')->nullable()->after('default_credits_per_class');
            $table->decimal('peak_time_multiplier', 3, 2)->nullable()->after('credit_rules');
            $table->json('peak_time_days')->nullable()->after('peak_time_multiplier');
            $table->time('peak_time_start')->nullable()->after('peak_time_days');
            $table->time('peak_time_end')->nullable()->after('peak_time_start');

            // Validity Period (Enhanced) - keep expires_after_days for backwards compat
            $table->enum('validity_type', ['days', 'months', 'no_expiration'])
                  ->default('days')
                  ->after('peak_time_end');
            $table->unsignedSmallInteger('validity_value')->nullable()->after('validity_type');

            // Expiry & Extension Rules
            $table->unsignedTinyInteger('grace_period_days')->default(0)->after('validity_value');
            $table->boolean('allow_admin_extension')->default(true)->after('grace_period_days');
            $table->boolean('allow_freeze')->default(false)->after('allow_admin_extension');
            $table->unsignedSmallInteger('max_freeze_days')->default(30)->after('allow_freeze');
            $table->decimal('reactivation_fee', 8, 2)->default(0)->after('max_freeze_days');
            $table->json('reactivation_fee_prices')->nullable()->after('reactivation_fee');

            // Sharing & Transfer
            $table->boolean('allow_transfer')->default(false)->after('reactivation_fee_prices');
            $table->boolean('allow_family_sharing')->default(false)->after('allow_transfer');
            $table->boolean('allow_gifting')->default(false)->after('allow_family_sharing');
            $table->unsignedTinyInteger('max_family_members')->default(0)->after('allow_gifting');

            // Auto-Renewal (Hybrid Model)
            $table->boolean('is_recurring')->default(false)->after('max_family_members');
            $table->enum('renewal_interval', ['weekly', 'bi_weekly', 'monthly'])->nullable()->after('is_recurring');
            $table->boolean('rollover_enabled')->default(false)->after('renewal_interval');
            $table->unsignedSmallInteger('max_rollover_credits')->default(0)->after('rollover_enabled');
            $table->unsignedTinyInteger('max_rollover_periods')->default(2)->after('max_rollover_credits');

            // Additional
            $table->string('color', 7)->nullable()->after('max_rollover_periods');
            $table->string('image_path', 255)->nullable()->after('color');

            // New member pricing
            $table->json('new_member_prices')->nullable()->after('prices');
        });

        // Step 4: Rename class_pack_purchases table to class_pass_purchases
        Schema::rename('class_pack_purchases', 'class_pass_purchases');

        // Step 5: Rename the foreign key column
        Schema::table('class_pass_purchases', function (Blueprint $table) {
            $table->renameColumn('class_pack_id', 'class_pass_id');
        });

        // Step 6: Add new columns to class_pass_purchases
        Schema::table('class_pass_purchases', function (Blueprint $table) {
            // Activation tracking
            $table->timestamp('activated_at')->nullable()->after('expires_at');
            $table->string('activation_type', 20)->nullable()->after('activated_at');

            // Freeze tracking
            $table->boolean('is_frozen')->default(false)->after('activation_type');
            $table->timestamp('frozen_at')->nullable()->after('is_frozen');
            $table->timestamp('freeze_expires_at')->nullable()->after('frozen_at');
            $table->unsignedSmallInteger('total_frozen_days')->default(0)->after('freeze_expires_at');

            // Rollover tracking
            $table->unsignedSmallInteger('rollover_credits')->default(0)->after('total_frozen_days');
            $table->unsignedSmallInteger('renewal_count')->default(0)->after('rollover_credits');

            // Transfer tracking
            $table->unsignedBigInteger('transferred_from_purchase_id')->nullable()->after('renewal_count');
            $table->unsignedBigInteger('transferred_to_client_id')->nullable()->after('transferred_from_purchase_id');
            $table->timestamp('transferred_at')->nullable()->after('transferred_to_client_id');

            // Stripe subscription (for recurring)
            $table->string('stripe_subscription_id', 255)->nullable()->after('transferred_at');

            // Credits used (for variable credit consumption)
            $table->unsignedSmallInteger('credits_used')->default(0)->after('classes_total');
        });

        // Step 7: Re-add foreign key constraint with new table name
        Schema::table('class_pass_purchases', function (Blueprint $table) {
            $table->foreign('class_pass_id')
                  ->references('id')
                  ->on('class_passes')
                  ->cascadeOnDelete();
        });

        // Step 8: Rename column in bookings table
        if (Schema::hasColumn('bookings', 'class_pack_purchase_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->renameColumn('class_pack_purchase_id', 'class_pass_purchase_id');
            });

            // Re-add the foreign key
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('class_pass_purchase_id')
                      ->references('id')
                      ->on('class_pass_purchases')
                      ->nullOnDelete();
            });
        }

        // Step 9: Create class_pass_credit_logs table
        Schema::create('class_pass_credit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_pass_purchase_id')
                  ->constrained('class_pass_purchases')
                  ->cascadeOnDelete();
            $table->foreignId('booking_id')
                  ->nullable()
                  ->constrained('bookings')
                  ->nullOnDelete();
            $table->smallInteger('credits_change'); // Can be positive (restore) or negative (deduct)
            $table->enum('credit_type', [
                'booking',
                'cancellation_restore',
                'admin_adjust',
                'rollover',
                'transfer_out',
                'transfer_in',
                'expiry_forfeit',
                'freeze_adjust'
            ]);
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['class_pass_purchase_id', 'created_at']);
        });

        // Step 10: Migrate expires_after_days to validity_value for existing records
        DB::statement("UPDATE class_passes SET validity_value = expires_after_days WHERE expires_after_days IS NOT NULL");
        DB::statement("UPDATE class_passes SET validity_type = 'no_expiration' WHERE expires_after_days IS NULL");

        // Step 11: Set activated_at for existing purchases (assume activated on purchase)
        DB::statement("UPDATE class_pass_purchases SET activated_at = purchased_at, activation_type = 'on_purchase' WHERE activated_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the credit logs table
        Schema::dropIfExists('class_pass_credit_logs');

        // Remove FK from bookings
        if (Schema::hasColumn('bookings', 'class_pass_purchase_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                try {
                    $table->dropForeign(['class_pass_purchase_id']);
                } catch (\Exception $e) {
                    // Continue if FK doesn't exist
                }
                $table->renameColumn('class_pass_purchase_id', 'class_pack_purchase_id');
            });
        }

        // Drop FK from class_pass_purchases
        Schema::table('class_pass_purchases', function (Blueprint $table) {
            $table->dropForeign(['class_pass_id']);
        });

        // Remove new columns from class_pass_purchases
        Schema::table('class_pass_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'activated_at',
                'activation_type',
                'is_frozen',
                'frozen_at',
                'freeze_expires_at',
                'total_frozen_days',
                'rollover_credits',
                'renewal_count',
                'transferred_from_purchase_id',
                'transferred_to_client_id',
                'transferred_at',
                'stripe_subscription_id',
                'credits_used',
            ]);
        });

        // Rename column back
        Schema::table('class_pass_purchases', function (Blueprint $table) {
            $table->renameColumn('class_pass_id', 'class_pack_id');
        });

        // Rename table back
        Schema::rename('class_pass_purchases', 'class_pack_purchases');

        // Remove new columns from class_passes
        Schema::table('class_passes', function (Blueprint $table) {
            $table->dropColumn([
                'activation_type',
                'eligibility_type',
                'eligible_categories',
                'eligible_instructor_ids',
                'eligible_location_ids',
                'excluded_class_types',
                'default_credits_per_class',
                'credit_rules',
                'peak_time_multiplier',
                'peak_time_days',
                'peak_time_start',
                'peak_time_end',
                'validity_type',
                'validity_value',
                'grace_period_days',
                'allow_admin_extension',
                'allow_freeze',
                'max_freeze_days',
                'reactivation_fee',
                'reactivation_fee_prices',
                'allow_transfer',
                'allow_family_sharing',
                'allow_gifting',
                'max_family_members',
                'is_recurring',
                'renewal_interval',
                'rollover_enabled',
                'max_rollover_credits',
                'max_rollover_periods',
                'color',
                'image_path',
                'new_member_prices',
            ]);
        });

        // Rename table back
        Schema::rename('class_passes', 'class_packs');

        // Re-add FK to class_pack_purchases
        Schema::table('class_pack_purchases', function (Blueprint $table) {
            $table->foreign('class_pack_id')
                  ->references('id')
                  ->on('class_packs')
                  ->cascadeOnDelete();
        });
    }
};
