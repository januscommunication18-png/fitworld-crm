<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['class_plans', 'service_plans', 'membership_plans', 'class_passes'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->json('file_attachments')->nullable();
            });
        }
    }

    public function down(): void
    {
        $tables = ['class_plans', 'service_plans', 'membership_plans', 'class_passes'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('file_attachments');
            });
        }
    }
};
