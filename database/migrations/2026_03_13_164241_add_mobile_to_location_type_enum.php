<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE locations MODIFY COLUMN location_type ENUM('in_person', 'public', 'virtual', 'mobile') DEFAULT 'in_person'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE locations MODIFY COLUMN location_type ENUM('in_person', 'public', 'virtual') DEFAULT 'in_person'");
    }
};
