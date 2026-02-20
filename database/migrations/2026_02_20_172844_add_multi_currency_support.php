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
        // Add default_currency to hosts
        Schema::table('hosts', function (Blueprint $table) {
            $table->string('default_currency', 3)->default('USD')->after('currencies');
        });

        // Add prices JSON column to membership_plans for multi-currency support
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->json('prices')->nullable()->after('price');
        });

        // Add prices JSON column to class_packs if it exists
        if (Schema::hasTable('class_packs')) {
            Schema::table('class_packs', function (Blueprint $table) {
                if (!Schema::hasColumn('class_packs', 'prices')) {
                    $table->json('prices')->nullable()->after('price');
                }
            });
        }

        // Add prices JSON column to intro_offers if it exists
        if (Schema::hasTable('intro_offers')) {
            Schema::table('intro_offers', function (Blueprint $table) {
                if (!Schema::hasColumn('intro_offers', 'prices')) {
                    $table->json('prices')->nullable()->after('price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropColumn('default_currency');
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn('prices');
        });

        if (Schema::hasTable('class_packs') && Schema::hasColumn('class_packs', 'prices')) {
            Schema::table('class_packs', function (Blueprint $table) {
                $table->dropColumn('prices');
            });
        }

        if (Schema::hasTable('intro_offers') && Schema::hasColumn('intro_offers', 'prices')) {
            Schema::table('intro_offers', function (Blueprint $table) {
                $table->dropColumn('prices');
            });
        }
    }
};
