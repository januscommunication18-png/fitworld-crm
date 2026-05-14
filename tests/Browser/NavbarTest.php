<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Host;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavbarTest extends DuskTestCase
{
    protected function getAuthenticatedUser()
    {
        $host = Host::whereNotNull('setup_completed_at')
            ->whereNotNull('onboarding_completed_at')->first();
        if (!$host) return null;

        return $host->users()->where('role', 'owner')->first()
            ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
    }

    /**
     * Test profile dropdown opens and shows name.
     */
    public function test_profile_dropdown_shows_name(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('#profile-dropdown-btn')
                ->waitFor('#profile-dropdown-menu:not(.hidden)')
                ->assertSee($user->full_name)
                ->assertSee($user->email);
        });
    }

    /**
     * Test profile dropdown has sign out button.
     */
    public function test_profile_dropdown_has_signout(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('#profile-dropdown-btn')
                ->waitFor('#profile-dropdown-menu:not(.hidden)')
                ->assertPresent('#profile-dropdown-menu button[title="Sign Out"]');
        });
    }

    /**
     * Test apps drawer links are disabled when setup incomplete.
     */
    public function test_apps_drawer_links_disabled_incomplete_setup(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNull('setup_completed_at')
                ->whereNotNull('onboarding_completed_at')->first();
            if (!$host) $this->markTestSkipped('No incomplete setup host');

            $user = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
            if (!$user) $this->markTestSkipped('No owner');

            $browser->loginAs($user)
                ->visit('/get-started')
                ->assertPresent('a[aria-label="Support"]');
        });
    }

    /**
     * Test sidebar disabled menus when setup incomplete.
     */
    public function test_sidebar_menus_disabled_incomplete_setup(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNull('setup_completed_at')
                ->whereNotNull('onboarding_completed_at')->first();
            if (!$host) $this->markTestSkipped('No incomplete setup host');

            $user = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
            if (!$user) $this->markTestSkipped('No owner');

            $browser->loginAs($user)
                ->visit('/get-started')
                // Dashboard should be disabled
                ->assertPresent('[data-nav="dashboard"].opacity-50')
                // Settings should NOT be disabled
                ->assertPresent('[data-nav="settings"]:not(.opacity-50)');
        });
    }
}
