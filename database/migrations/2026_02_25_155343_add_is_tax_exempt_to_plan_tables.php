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
        Schema::table('class_plans', function (Blueprint $table) {
            $table->boolean('is_tax_exempt')->default(false)->after('is_active');
        });

        Schema::table('service_plans', function (Blueprint $table) {
            $table->boolean('is_tax_exempt')->default(false)->after('is_active');
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->boolean('is_tax_exempt')->default(false)->after('status');
        });

        Schema::table('class_packs', function (Blueprint $table) {
            $table->boolean('is_tax_exempt')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_plans', function (Blueprint $table) {
            $table->dropColumn('is_tax_exempt');
        });

        Schema::table('service_plans', function (Blueprint $table) {
            $table->dropColumn('is_tax_exempt');
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn('is_tax_exempt');
        });

        Schema::table('class_packs', function (Blueprint $table) {
            $table->dropColumn('is_tax_exempt');
        });
    }
};
