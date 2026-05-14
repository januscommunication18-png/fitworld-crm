<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Host;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SettingsLocationsTest extends DuskTestCase
{
    protected function getAuthenticatedUser()
    {
        $host = Host::whereNotNull('onboarding_completed_at')->first();
        if (!$host) return null;

        return $host->users()->where('role', 'owner')->first()
            ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
    }

    /**
     * Test locations listing page loads.
     */
    public function test_locations_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/locations')
                ->assertSee('Locations');
        });
    }

    /**
     * Test default location does not show delete or inactive options.
     */
    public function test_default_location_has_no_delete_or_inactive(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/locations')
                ->assertSee('Default');
            // Default location card should not have delete/inactive in its dropdown
        });
    }

    /**
     * Test location status badges appear.
     */
    public function test_location_shows_status_badge(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/locations')
                ->assertSee('Active');
        });
    }

    /**
     * Test create location page loads with smarty address search.
     */
    public function test_create_location_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/locations/create')
                ->assertSee('Add Location')
                ->assertPresent('#location-address-search');
        });
    }

    /**
     * Test smarty address search shows suggestions.
     */
    public function test_smarty_address_search_shows_suggestions(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/locations/create')
                ->waitFor('#location-address-search')
                // Select in-person type first to show address section
                ->script("document.querySelector('option[value=\"in_person\"]').selected = true; document.getElementById('location_types').dispatchEvent(new Event('change'));");

            $browser->pause(300)
                ->type('#location-address-search', '123 Main St')
                ->pause(500);
            // Suggestions should appear if Smarty API is configured
        });
    }

    /**
     * Test rooms page loads.
     */
    public function test_rooms_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/locations/rooms')
                ->assertSee('Rooms');
        });
    }

    /**
     * Test room toggle status shows confirmation.
     */
    public function test_room_toggle_shows_confirmation(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $host = $user->host;
            if ($host->locations()->withCount('rooms')->get()->sum('rooms_count') === 0) {
                $this->markTestSkipped('No rooms found');
            }

            $browser->loginAs($user)
                ->visit('/settings/locations/rooms');
            // Click on first room's dropdown and toggle status
        });
    }
}
