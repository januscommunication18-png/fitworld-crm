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
        Schema::table('class_requests', function (Blueprint $table) {
            $table->foreignId('helpdesk_ticket_id')->nullable()->after('client_id')->constrained('helpdesk_tickets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_requests', function (Blueprint $table) {
            $table->dropForeign(['helpdesk_ticket_id']);
            $table->dropColumn('helpdesk_ticket_id');
        });
    }
};
