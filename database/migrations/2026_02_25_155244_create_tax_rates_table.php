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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->nullable()->constrained()->cascadeOnDelete();

            // Location
            $table->char('country_code', 2);
            $table->string('state_code', 10)->nullable();
            $table->string('city', 100)->nullable();

            // Tax info
            $table->string('tax_name', 100);
            $table->string('tax_type', 50); // sales_tax, vat, gst, pst, hst, cgst, sgst, igst, iva, qst
            $table->decimal('rate', 6, 3); // Allows up to 999.999%

            // Calculation rules
            $table->boolean('is_compound')->default(false);
            $table->integer('priority')->default(0);

            // Applicability
            $table->json('applies_to')->nullable(); // ["class", "service", "membership", "pack"] or null for all

            // Effective dates
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['country_code', 'state_code'], 'idx_country_state');
            $table->index(['host_id', 'country_code'], 'idx_host_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
