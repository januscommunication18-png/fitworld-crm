<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('host_user', function (Blueprint $table) {
            $table->string('personal_override_code', 10)->nullable()->unique()->after('permissions');
        });

        // Generate codes for users with pricing.override permission or owner/admin role
        $hostUsers = DB::table('host_user')->get();

        foreach ($hostUsers as $hostUser) {
            $permissions = json_decode($hostUser->permissions, true) ?? [];
            $hasPricingOverride = in_array('pricing.override', $permissions);
            $isOwnerOrAdmin = in_array($hostUser->role, ['owner', 'admin']);

            if ($hasPricingOverride || $isOwnerOrAdmin) {
                $code = $this->generateUniqueCode();
                DB::table('host_user')
                    ->where('id', $hostUser->id)
                    ->update(['personal_override_code' => $code]);
            }
        }
    }

    /**
     * Generate a unique personal override code
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = 'MY-' . strtoupper(Str::random(5));
        } while (DB::table('host_user')->where('personal_override_code', $code)->exists());

        return $code;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('host_user', function (Blueprint $table) {
            $table->dropColumn('personal_override_code');
        });
    }
};
