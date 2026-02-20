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
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('hide_from_books')->default(false)->after('notes');
            $table->timestamp('hidden_at')->nullable()->after('hide_from_books');
            $table->unsignedBigInteger('hidden_by')->nullable()->after('hidden_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['hide_from_books', 'hidden_at', 'hidden_by']);
        });
    }
};
