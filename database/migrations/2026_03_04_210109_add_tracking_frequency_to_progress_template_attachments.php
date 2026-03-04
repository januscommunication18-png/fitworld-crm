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
        Schema::table('progress_template_attachments', function (Blueprint $table) {
            $table->enum('tracking_frequency', [
                'every_class',
                'daily',
                'weekly',
                'biweekly',
                'monthly',
                'custom'
            ])->default('every_class')->after('trigger_point');
            $table->unsignedSmallInteger('tracking_interval_days')->nullable()->after('tracking_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_template_attachments', function (Blueprint $table) {
            $table->dropColumn(['tracking_frequency', 'tracking_interval_days']);
        });
    }
};
