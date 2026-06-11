<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Human-readable Client Token ID (e.g. ZYS-SARAH-84922) — the client's
     * login identifier in the branded client app. Generated lazily.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('client_code', 40)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('client_code');
        });
    }
};
