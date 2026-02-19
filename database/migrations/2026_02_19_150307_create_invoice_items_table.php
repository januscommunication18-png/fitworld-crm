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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            // Polymorphic relationship for what was invoiced
            // e.g., ClassSession, ServiceSlot, MembershipPlan, ClassPack
            $table->nullableMorphs('itemable');

            // Item details
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);

            // Sort order for display
            $table->integer('sort_order')->default(0);

            // Metadata for additional info
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Index
            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
