<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Helpdesk requests can target any catalog offering — Class Plan, Service Plan,
 * Class Pass, Membership, Rental Space, Item Rental, or Event. We add a
 * polymorphic pair (`requested_type` + `requested_id`) and keep the legacy
 * `service_plan_id` column for back-compat with rows that pre-date this change.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->nullableMorphs('requested');
        });
    }

    public function down(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->dropMorphs('requested');
        });
    }
};
