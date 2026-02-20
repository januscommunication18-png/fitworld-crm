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
        // 1. Rental Items Catalog
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->json('images')->nullable();
            $table->json('prices')->nullable();
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->json('deposit_prices')->nullable();
            $table->integer('total_inventory')->default(0);
            $table->integer('available_inventory')->default(0);
            $table->boolean('requires_return')->default(true);
            $table->integer('max_rental_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['host_id', 'is_active']);
            $table->index(['host_id', 'category']);
        });

        // 2. Rental Item Eligibility (who can rent)
        Schema::create('rental_item_eligibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_item_id')->constrained()->cascadeOnDelete();
            $table->string('eligible_type'); // 'all', 'membership', 'class_pack'
            $table->foreignId('membership_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('class_pack_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_free')->default(false);
            $table->timestamps();
        });

        // 3. Rental Item <-> Class Plan pivot (suggested/required items per class)
        Schema::create('rental_item_class_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_plan_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['rental_item_id', 'class_plan_id']);
        });

        // 4. Rental Bookings
        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('bookable');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->date('rental_date');
            $table->date('due_date')->nullable();
            $table->enum('fulfillment_status', ['pending', 'prepared', 'handed_out', 'returned', 'lost'])->default('pending');
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('handed_out_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('handed_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('condition_on_return', ['good', 'damaged', 'lost'])->nullable();
            $table->text('damage_notes')->nullable();
            $table->decimal('damage_charge', 10, 2)->default(0);
            $table->boolean('deposit_refunded')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['host_id', 'rental_date']);
            $table->index(['host_id', 'fulfillment_status']);
            $table->index(['rental_item_id', 'rental_date']);
        });

        // 5. Rental Inventory Logs
        Schema::create('rental_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_booking_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action', ['booked', 'returned', 'damaged', 'lost', 'adjustment', 'restock']);
            $table->integer('quantity_change');
            $table->integer('inventory_after');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['rental_item_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_inventory_logs');
        Schema::dropIfExists('rental_bookings');
        Schema::dropIfExists('rental_item_class_plan');
        Schema::dropIfExists('rental_item_eligibility');
        Schema::dropIfExists('rental_items');
    }
};
