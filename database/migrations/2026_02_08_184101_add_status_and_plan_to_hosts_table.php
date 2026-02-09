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
        Schema::table('hosts', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'pending_verify', 'suspended'])
                ->default('pending_verify')
                ->after('is_live');
            $table->timestamp('verified_at')->nullable()->after('status');
            $table->foreignId('plan_id')->nullable()->after('verified_at')->constrained()->nullOnDelete();
            $table->enum('subscription_status', ['trialing', 'active', 'past_due', 'canceled'])
                ->nullable()
                ->after('plan_id');
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosts', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn([
                'status',
                'verified_at',
                'plan_id',
                'subscription_status',
                'trial_ends_at',
                'subscription_ends_at',
            ]);
        });
    }
};
