<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Host;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    /**
     * Test dashboard redirects owner with incomplete setup to get-started.
     */
    public function test_owner_with_incomplete_setup_redirected_to_get_started(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNull('setup_completed_at')->first();
            if (!$host) {
                $this->markTestSkipped('No host with incomplete setup found');
            }

            $user = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();

            if (!$user) {
                $this->markTestSkipped('No owner user found');
            }

            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertPathIs('/get-started');
        });
    }

    /**
     * Test get-started page shows setup checklist.
     */
    public function test_get_started_page_shows_checklist(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNull('setup_completed_at')->first();
            if (!$host) {
                $this->markTestSkipped('No host with incomplete setup found');
            }

            $user = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();

            if (!$user) {
                $this->markTestSkipped('No owner user found');
            }

            $browser->loginAs($user)
                ->visit('/get-started')
                ->assertSee('Setup Checklist')
                ->assertSee('Verify Email');
        });
    }

    /**
     * Test sidebar shows Get Started nav item when setup is incomplete.
     */
    public function test_sidebar_shows_get_started_for_incomplete_setup(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNull('setup_completed_at')->first();
            if (!$host) {
                $this->markTestSkipped('No host with incomplete setup found');
            }

            $user = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();

            if (!$user) {
                $this->markTestSkipped('No owner user found');
            }

            $browser->loginAs($user)
                ->visit('/get-started')
                ->assertSee('Get Started');
        });
    }

    /**
     * Test dashboard loads for user with complete setup.
     */
    public function test_dashboard_loads_for_completed_setup(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNotNull('setup_completed_at')
                ->whereNotNull('onboarding_completed_at')
                ->first();

            if (!$host) {
                $this->markTestSkipped('No host with completed setup found');
            }

            $user = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();

            if (!$user) {
                $this->markTestSkipped('No owner user found');
            }

            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Dashboard');
        });
    }
}
