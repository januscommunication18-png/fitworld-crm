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
        // Change ENUM to VARCHAR to support multiple values (comma-separated)
        DB::statement("ALTER TABLE clients MODIFY preferred_contact_method VARCHAR(50) DEFAULT 'email'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (will lose multi-value data)
        DB::statement("ALTER TABLE clients MODIFY preferred_contact_method ENUM('email', 'phone', 'sms') DEFAULT 'email'");
    }
};
