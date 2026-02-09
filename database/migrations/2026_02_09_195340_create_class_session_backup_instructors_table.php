<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_session_backup_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('priority')->default(1); // Order of backup preference
            $table->timestamps();

            // Unique constraint - same instructor can't be backup twice for same session
            $table->unique(['class_session_id', 'instructor_id'], 'session_backup_instructor_unique');

            // Index for lookups
            $table->index(['class_session_id', 'priority'], 'session_backup_priority_idx');
        });

        // Migrate existing backup_instructor_id data to new table
        $sessions = DB::table('class_sessions')
            ->whereNotNull('backup_instructor_id')
            ->get(['id', 'backup_instructor_id']);

        foreach ($sessions as $session) {
            DB::table('class_session_backup_instructors')->insert([
                'class_session_id' => $session->id,
                'instructor_id' => $session->backup_instructor_id,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_session_backup_instructors');
    }
};
