<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: relax the enum to a string so we can remap values without enum check failures.
        DB::statement("ALTER TABLE clients MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'active'");

        // Step 2: remap legacy values.
        // lead   → inactive (signed up but not yet engaged)
        // client → active   (engaged customer)
        // member → active   (engaged customer; membership_status column retains the member detail)
        // at_risk stays.
        DB::table('clients')->where('status', 'lead')->update(['status' => 'inactive']);
        DB::table('clients')->whereIn('status', ['client', 'member'])->update(['status' => 'active']);

        // Step 3: tighten back to the new enum.
        DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('active', 'inactive', 'at_risk') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Relax to string, reverse-map, then tighten back to the old enum.
        // Note: 'member' detail is lost in the new schema — anyone now 'active' restores as 'client'
        // unless their membership_status is 'active', in which case restore as 'member'.
        DB::statement("ALTER TABLE clients MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'client'");

        DB::table('clients')->where('status', 'inactive')->update(['status' => 'lead']);
        DB::table('clients')
            ->where('status', 'active')
            ->where('membership_status', 'active')
            ->update(['status' => 'member']);
        DB::table('clients')->where('status', 'active')->update(['status' => 'client']);

        DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('lead', 'client', 'member', 'at_risk') NOT NULL DEFAULT 'client'");
    }
};
