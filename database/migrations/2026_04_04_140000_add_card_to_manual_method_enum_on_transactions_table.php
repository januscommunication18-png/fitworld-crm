<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `manual_method` ENUM('cash','check','venmo','zelle','paypal','cash_app','bank_transfer','card','other') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `manual_method` ENUM('cash','check','venmo','zelle','paypal','cash_app','bank_transfer','other') NULL");
    }
};
