<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('host_id')->nullable()->after('id')->constrained('hosts')->cascadeOnDelete();
            $table->string('first_name')->after('host_id');
            $table->string('last_name')->after('first_name');
            $table->string('role')->default('owner')->after('password');
            $table->boolean('is_instructor')->default(false)->after('role');

            $table->dropColumn('name');

            $table->index('host_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->dropForeign(['host_id']);
            $table->dropColumn(['host_id', 'first_name', 'last_name', 'role', 'is_instructor']);
        });
    }
};
