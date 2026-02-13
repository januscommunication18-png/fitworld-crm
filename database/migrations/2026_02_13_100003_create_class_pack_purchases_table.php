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
        Schema::create('class_pack_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('class_pack_id')->constrained('class_packs')->cascadeOnDelete();

            // Credits tracking
            $table->unsignedSmallInteger('classes_remaining');
            $table->unsignedSmallInteger('classes_total'); // Original count for reference

            // Dates
            $table->timestamp('purchased_at');
            $table->timestamp('expires_at')->nullable();

            // Payment reference (will be set after payments table exists)
            $table->unsignedBigInteger('payment_id')->nullable();

            // Who sold this pack
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'client_id']);
            $table->index(['client_id', 'classes_remaining']); // For finding active packs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_pack_purchases');
    }
};
