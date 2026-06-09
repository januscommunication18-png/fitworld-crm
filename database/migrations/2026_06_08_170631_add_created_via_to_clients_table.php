<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records the channel a client was created from: `web`, `mobile`, `api`,
     * etc. Null for legacy rows created before this column existed.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('created_via')->nullable()->after('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('created_via');
        });
    }
};
