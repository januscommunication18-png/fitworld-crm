<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            // Team members (users on this host) who should be notified when a customer
            // submits an info request for this class.
            $table->json('notification_user_ids')->nullable()->after('file_attachments');
        });
    }

    public function down(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->dropColumn('notification_user_ids');
        });
    }
};
