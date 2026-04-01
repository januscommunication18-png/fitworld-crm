<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('source_type'); // class_plan, service_plan
            $table->unsignedBigInteger('source_id'); // class_plan_id or service_plan_id
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->integer('billing_period'); // months: 1, 3, 6, 9, 12
            $table->decimal('discount_percent', 5, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('monthly_rate', 10, 2);
            $table->decimal('original_monthly_rate', 10, 2);
            $table->decimal('credit_remaining', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'exhausted', 'expired', 'cancelled'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['host_id', 'client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_credits');
    }
};
