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
        Schema::table('users', function (Blueprint $table) {
            // Status: active, invited, suspended, deactivated
            $table->string('status')->default('active')->after('is_instructor');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->json('permissions')->nullable()->after('last_login_at');
            $table->foreignId('instructor_id')->nullable()->after('permissions')
                ->constrained('instructors')->nullOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropColumn(['status', 'last_login_at', 'permissions', 'instructor_id', 'deleted_at']);
        });
    }
};
