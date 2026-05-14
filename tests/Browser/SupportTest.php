<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Host;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SupportTest extends DuskTestCase
{
    protected function getAuthenticatedUser()
    {
        $host = Host::whereNotNull('onboarding_completed_at')->first();
        if (!$host) return null;

        return $host->users()->where('role', 'owner')->first()
            ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
    }

    /**
     * Test support page loads in standalone layout.
     */
    public function test_support_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/support/requests')
                ->assertSee('Support')
                ->assertSee('My Support Requests')
                // Should have close button (X) in top bar
                ->assertPresent('a[title="Close"]');
        });
    }

    /**
     * Test support page has no sidebar.
     */
    public function test_support_page_has_no_sidebar(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/support/requests')
                ->assertMissing('#main-sidebar');
        });
    }

    /**
     * Test close button on support page goes to dashboard.
     */
    public function test_close_button_goes_to_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/support/requests')
                ->click('a[title="Close"]')
                ->waitForLocation('/dashboard')
                ->assertPathBeginsWith('/');
        });
    }

    /**
     * Test support icon shows in navbar.
     */
    public function test_support_icon_in_navbar(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $host = Host::whereNotNull('setup_completed_at')
                ->whereNotNull('onboarding_completed_at')->first();
            if (!$host) $this->markTestSkipped('No completed host');

            $owner = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
            if (!$owner) $this->markTestSkipped('No owner');

            $browser->loginAs($owner)
                ->visit('/dashboard')
                ->assertPresent('a[aria-label="Support"]');
        });
    }

    /**
     * Test new request drawer opens with readonly fields.
     */
    public function test_new_request_drawer_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/support/requests')
                ->click('[onclick*="openSupportModal"]')
                ->waitFor('#support-drawer:not(.translate-x-full)')
                ->assertSee('Request Technical Support')
                // First name, last name, email should be readonly
                ->assertAttribute('#support_first_name', 'readonly', 'true')
                ->assertAttribute('#support_last_name', 'readonly', 'true')
                ->assertAttribute('#support_email', 'readonly', 'true');
        });
    }
}
