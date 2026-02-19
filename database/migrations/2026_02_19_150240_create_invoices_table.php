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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

            // Invoice number (format: INV-{STUDIO_CODE}-{YYYYMM}-{SEQ})
            $table->string('invoice_number', 30)->unique();

            // Status
            $table->enum('status', [
                'draft',
                'sent',
                'paid',
                'void',
                'refunded',
            ])->default('draft');

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Dates
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            // PDF storage path
            $table->string('pdf_path')->nullable();

            // Billing info snapshot (in case client info changes)
            $table->json('billing_info')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['host_id', 'client_id']);
            $table->index(['host_id', 'status']);
            $table->index(['host_id', 'issue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
