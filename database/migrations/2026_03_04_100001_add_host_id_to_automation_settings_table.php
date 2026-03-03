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
        Schema::table('automation_settings', function (Blueprint $table) {
            $table->foreignId('host_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->dropUnique(['key']); // Remove unique constraint on key alone
            $table->unique(['host_id', 'key']); // Add composite unique constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automation_settings', function (Blueprint $table) {
            $table->dropUnique(['host_id', 'key']);
            $table->dropForeign(['host_id']);
            $table->dropColumn('host_id');
            $table->unique(['key']);
        });
    }
};
