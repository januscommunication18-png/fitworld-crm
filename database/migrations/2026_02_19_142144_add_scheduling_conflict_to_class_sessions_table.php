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
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->boolean('has_scheduling_conflict')->default(false)->after('status');
            $table->text('conflict_notes')->nullable()->after('has_scheduling_conflict');
            $table->timestamp('conflict_resolved_at')->nullable()->after('conflict_notes');
            $table->foreignId('conflict_resolved_by')->nullable()->after('conflict_resolved_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropForeign(['conflict_resolved_by']);
            $table->dropColumn([
                'has_scheduling_conflict',
                'conflict_notes',
                'conflict_resolved_at',
                'conflict_resolved_by',
            ]);
        });
    }
};
