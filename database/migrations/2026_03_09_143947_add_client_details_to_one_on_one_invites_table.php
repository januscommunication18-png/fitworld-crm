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
        Schema::table('one_on_one_invites', function (Blueprint $table) {
            if (!Schema::hasColumn('one_on_one_invites', 'client_name')) {
                $table->string('client_name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('one_on_one_invites', 'duration')) {
                $table->unsignedSmallInteger('duration')->nullable()->after('client_name');
            }
            if (!Schema::hasColumn('one_on_one_invites', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_on_one_invites', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'duration', 'scheduled_at']);
        });
    }
};
