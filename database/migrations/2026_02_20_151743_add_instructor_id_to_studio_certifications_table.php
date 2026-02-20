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
            $table->foreignId('instructor_id')->nullable()->after('host_id')->constrained()->onDelete('cascade');
            $table->index(['host_id', 'instructor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('studio_certifications', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropIndex(['host_id', 'instructor_id']);
            $table->dropColumn('instructor_id');
        });
    }
};
