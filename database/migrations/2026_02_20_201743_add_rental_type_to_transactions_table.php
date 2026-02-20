<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('class_booking', 'service_booking', 'membership_purchase', 'class_pack_purchase', 'rental')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('class_booking', 'service_booking', 'membership_purchase', 'class_pack_purchase')");
    }
};
