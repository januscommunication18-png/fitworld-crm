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
        Schema::table('locations', function (Blueprint $table) {
            // Location type (required)
            $table->enum('location_type', ['in_person', 'public', 'virtual'])
                ->default('in_person')
                ->after('name');

            // Make address fields nullable for virtual locations
            $table->string('address_line_1')->nullable()->change();
            $table->string('postal_code')->nullable()->change();

            // Public location fields
            $table->text('public_location_notes')->nullable()->after('notes');

            // Virtual location fields
            $table->string('virtual_platform')->nullable()->after('public_location_notes');
            $table->string('virtual_meeting_link')->nullable()->after('virtual_platform');
            $table->text('virtual_access_notes')->nullable()->after('virtual_meeting_link');
            $table->boolean('hide_link_until_booking')->default(true)->after('virtual_access_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'location_type',
                'public_location_notes',
                'virtual_platform',
                'virtual_meeting_link',
                'virtual_access_notes',
                'hide_link_until_booking',
            ]);

            // Restore required constraints
            $table->string('address_line_1')->nullable(false)->change();
            $table->string('postal_code')->nullable(false)->change();
        });
    }
};
