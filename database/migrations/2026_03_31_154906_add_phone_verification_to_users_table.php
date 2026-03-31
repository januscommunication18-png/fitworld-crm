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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_country_code', 5)->nullable()->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_country_code');
            $table->string('phone_verification_code', 6)->nullable()->after('phone_verified_at');
            $table->timestamp('phone_verification_expires_at')->nullable()->after('phone_verification_code');
            $table->unsignedTinyInteger('phone_verification_attempts')->default(0)->after('phone_verification_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_country_code',
                'phone_verified_at',
                'phone_verification_code',
                'phone_verification_expires_at',
                'phone_verification_attempts',
            ]);
        });
    }
};
