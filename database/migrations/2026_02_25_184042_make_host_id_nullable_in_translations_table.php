<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes host_id nullable to support global translations (host_id = NULL).
     */
    public function up(): void
    {
        Schema::table('translations', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['host_id']);

            // Drop the existing unique constraint
            $table->dropUnique(['host_id', 'translation_key']);
        });

        // Make host_id nullable
        Schema::table('translations', function (Blueprint $table) {
            $table->unsignedBigInteger('host_id')->nullable()->change();
        });

        Schema::table('translations', function (Blueprint $table) {
            // Re-add foreign key that allows NULL
            $table->foreign('host_id')
                ->references('id')
                ->on('hosts')
                ->onDelete('cascade');

            // Add a unique index - MySQL allows multiple NULLs in unique indexes
            // For global translations (NULL host_id), the key must be unique among globals
            // For studio translations, the key must be unique per studio
            $table->unique(['host_id', 'translation_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->dropForeign(['host_id']);
            $table->dropUnique(['host_id', 'translation_key']);
        });

        // Make host_id NOT NULL again
        Schema::table('translations', function (Blueprint $table) {
            $table->unsignedBigInteger('host_id')->nullable(false)->change();
        });

        Schema::table('translations', function (Blueprint $table) {
            $table->foreign('host_id')
                ->references('id')
                ->on('hosts')
                ->onDelete('cascade');

            $table->unique(['host_id', 'translation_key']);
        });
    }
};
