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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('checked_in_by_user_id')->nullable()->after('checked_in_at')->constrained('users')->nullOnDelete();
            $table->string('checked_in_method', 20)->nullable()->after('checked_in_by_user_id'); // staff, self, card_reader
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by_user_id']);
            $table->dropColumn(['checked_in_by_user_id', 'checked_in_method']);
        });
    }
};
