<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_memberships', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_memberships', function (Blueprint $table) {
            $table->dropColumn('access_token');
        });
    }
};
