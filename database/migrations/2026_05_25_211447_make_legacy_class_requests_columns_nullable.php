<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Legacy columns that are NOT NULL with no default; the model no longer writes to them.
        DB::statement("ALTER TABLE class_requests MODIFY COLUMN requester_name VARCHAR(255) NULL");
        DB::statement("ALTER TABLE class_requests MODIFY COLUMN requester_email VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // Restore the original NOT NULL constraint. Backfill nulls with empty strings
        // so the constraint can be re-applied without erroring on existing rows.
        DB::statement("UPDATE class_requests SET requester_name = '' WHERE requester_name IS NULL");
        DB::statement("UPDATE class_requests SET requester_email = '' WHERE requester_email IS NULL");
        DB::statement("ALTER TABLE class_requests MODIFY COLUMN requester_name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE class_requests MODIFY COLUMN requester_email VARCHAR(255) NOT NULL");
    }
};
