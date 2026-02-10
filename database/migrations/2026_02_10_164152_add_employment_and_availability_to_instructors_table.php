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
        Schema::table('instructors', function (Blueprint $table) {
            // Section 2: Employment Details
            $table->string('employment_type')->nullable()->after('is_active');
            $table->string('rate_type')->nullable()->after('employment_type');
            $table->decimal('rate_amount', 8, 2)->nullable()->after('rate_type');
            $table->text('compensation_notes')->nullable()->after('rate_amount');

            // Section 3: Workload & Allocation
            $table->decimal('hours_per_week', 5, 2)->nullable()->after('compensation_notes');
            $table->unsignedSmallInteger('max_classes_per_week')->nullable()->after('hours_per_week');

            // Section 4: Working Days
            $table->json('working_days')->nullable()->after('max_classes_per_week');

            // Section 5: Default Daily Availability
            $table->time('availability_default_from')->nullable()->after('working_days');
            $table->time('availability_default_to')->nullable()->after('availability_default_from');
            $table->json('availability_by_day')->nullable()->after('availability_default_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn([
                'employment_type',
                'rate_type',
                'rate_amount',
                'compensation_notes',
                'hours_per_week',
                'max_classes_per_week',
                'working_days',
                'availability_default_from',
                'availability_default_to',
                'availability_by_day',
            ]);
        });
    }
};
