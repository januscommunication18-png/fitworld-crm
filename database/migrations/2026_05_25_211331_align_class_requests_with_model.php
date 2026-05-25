<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('class_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('class_requests', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('host_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('class_requests', 'class_session_id')) {
                $table->foreignId('class_session_id')->nullable()->after('service_plan_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('class_requests', 'first_name')) {
                $table->string('first_name', 100)->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('class_requests', 'last_name')) {
                $table->string('last_name', 100)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('class_requests', 'email')) {
                $table->string('email')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('class_requests', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('class_requests', 'message')) {
                $table->text('message')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('class_requests', 'waitlist_requested')) {
                $table->boolean('waitlist_requested')->default(false)->after('message');
            }
            if (!Schema::hasColumn('class_requests', 'source')) {
                $table->string('source', 50)->default('web')->after('waitlist_requested');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_requests', function (Blueprint $table) {
            foreach (['source', 'waitlist_requested', 'message', 'phone', 'email', 'last_name', 'first_name'] as $col) {
                if (Schema::hasColumn('class_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('class_requests', 'class_session_id')) {
                $table->dropConstrainedForeignId('class_session_id');
            }
            if (Schema::hasColumn('class_requests', 'client_id')) {
                $table->dropConstrainedForeignId('client_id');
            }
        });
    }
};
