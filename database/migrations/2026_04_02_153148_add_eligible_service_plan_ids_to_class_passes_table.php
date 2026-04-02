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
        Schema::table('class_passes', function (Blueprint $table) {
            $table->json('eligible_service_plan_ids')->nullable()->after('eligible_location_ids');
        });
    }

    public function down(): void
    {
        Schema::table('class_passes', function (Blueprint $table) {
            $table->dropColumn('eligible_service_plan_ids');
        });
    }
};
