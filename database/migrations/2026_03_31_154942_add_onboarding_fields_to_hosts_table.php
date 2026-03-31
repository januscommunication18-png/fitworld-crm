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
        Schema::table('hosts', function (Blueprint $table) {
            $table->string('studio_structure')->nullable()->after('studio_name'); // 'solo' or 'with_team'
            $table->boolean('support_requested')->default(false)->after('onboarding_completed_at');
            $table->timestamp('support_requested_at')->nullable()->after('support_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn([
                'studio_structure',
                'support_requested',
                'support_requested_at',
            ]);
        });
    }
};
