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
        Schema::table('prospect_waitlists', function (Blueprint $table) {
            $table->string('member_size')->nullable()->after('studio_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospect_waitlists', function (Blueprint $table) {
            $table->dropColumn('member_size');
        });
    }
};
