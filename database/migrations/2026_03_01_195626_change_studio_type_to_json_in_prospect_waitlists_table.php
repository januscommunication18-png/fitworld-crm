<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First convert existing string values to JSON arrays
        DB::table('prospect_waitlists')
            ->whereNotNull('studio_type')
            ->where('studio_type', '!=', '')
            ->update([
                'studio_type' => DB::raw("JSON_ARRAY(studio_type)")
            ]);

        // Set empty/null values to empty JSON array
        DB::table('prospect_waitlists')
            ->where(function ($query) {
                $query->whereNull('studio_type')
                    ->orWhere('studio_type', '');
            })
            ->update(['studio_type' => '[]']);

        // Now change column type
        Schema::table('prospect_waitlists', function (Blueprint $table) {
            $table->json('studio_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Convert JSON array back to first value string
        DB::table('prospect_waitlists')
            ->whereNotNull('studio_type')
            ->update([
                'studio_type' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(studio_type, '$[0]'))")
            ]);

        Schema::table('prospect_waitlists', function (Blueprint $table) {
            $table->string('studio_type')->nullable()->change();
        });
    }
};
