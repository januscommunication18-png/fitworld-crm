<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            if (!Schema::hasColumn('hosts', 'studio_categories')) {
                $table->json('studio_categories')->nullable();
            }
            if (!Schema::hasColumn('hosts', 'studio_structure')) {
                $table->string('studio_structure')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn(['studio_structure', 'studio_categories']);
        });
    }
};
