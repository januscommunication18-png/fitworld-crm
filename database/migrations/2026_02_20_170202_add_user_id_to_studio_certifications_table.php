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
        Schema::table('studio_certifications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('instructor_id')->constrained()->onDelete('cascade');
            $table->index(['host_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('studio_certifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['host_id', 'user_id']);
            $table->dropColumn('user_id');
        });
    }
};
