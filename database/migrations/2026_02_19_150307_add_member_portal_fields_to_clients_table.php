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
        Schema::table('clients', function (Blueprint $table) {
            // Password for member portal (nullable - only used if password login mode)
            $table->string('password')->nullable()->after('email');

            // OTP activation code (for email+code login mode)
            $table->string('activation_code', 6)->nullable()->after('password');
            $table->timestamp('activation_code_expires_at')->nullable()->after('activation_code');

            // Portal activity tracking
            $table->timestamp('portal_last_login_at')->nullable()->after('activation_code_expires_at');
            $table->unsignedInteger('portal_login_count')->default(0)->after('portal_last_login_at');

            // Email verification for portal
            $table->timestamp('portal_email_verified_at')->nullable()->after('portal_login_count');

            // Remember token for persistent sessions
            $table->rememberToken()->after('portal_email_verified_at');

            // Password reset token
            $table->string('password_reset_token', 64)->nullable()->after('remember_token');
            $table->timestamp('password_reset_expires_at')->nullable()->after('password_reset_token');

            // Rate limiting for OTP
            $table->unsignedTinyInteger('otp_attempts')->default(0)->after('password_reset_expires_at');
            $table->timestamp('otp_locked_until')->nullable()->after('otp_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'activation_code',
                'activation_code_expires_at',
                'portal_last_login_at',
                'portal_login_count',
                'portal_email_verified_at',
                'remember_token',
                'password_reset_token',
                'password_reset_expires_at',
                'otp_attempts',
                'otp_locked_until',
            ]);
        });
    }
};
