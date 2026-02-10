<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new currencies column if it doesn't exist
        if (!Schema::hasColumn('hosts', 'currencies')) {
            Schema::table('hosts', function (Blueprint $table) {
                $table->json('currencies')->nullable()->after('country');
            });
        }

        // Migrate existing currency data to currencies array
        if (Schema::hasColumn('hosts', 'currency')) {
            DB::table('hosts')->whereNotNull('currency')->orderBy('id')->each(function ($host) {
                DB::table('hosts')
                    ->where('id', $host->id)
                    ->update(['currencies' => json_encode([$host->currency])]);
            });

            // Drop old currency column
            Schema::table('hosts', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back currency column
        Schema::table('hosts', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('country');
        });

        // Migrate currencies back to single currency (take first one)
        DB::table('hosts')->whereNotNull('currencies')->orderBy('id')->each(function ($host) {
            $currencies = json_decode($host->currencies, true);
            $currency = !empty($currencies) ? $currencies[0] : 'USD';
            DB::table('hosts')
                ->where('id', $host->id)
                ->update(['currency' => $currency]);
        });

        // Drop currencies column
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn('currencies');
        });
    }
};
