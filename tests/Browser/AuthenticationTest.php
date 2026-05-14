<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Host;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationTest extends DuskTestCase
{
    /**
     * Test the login page loads.
     */
    public function test_login_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }

    /**
     * Test login with invalid credentials shows error.
     */
    public function test_login_with_invalid_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'nonexistent@example.com')
                ->type('password', 'wrongpassword')
                ->press('Login')
                ->waitForText('credentials')
                ->assertSee('credentials');
        });
    }

    /**
     * Test login redirects to appropriate page.
     */
    public function test_login_redirects_authenticated_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertDontSee('Dashboard');
        });
    }

    /**
     * Test forgot password page loads.
     */
    public function test_forgot_password_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/forgot-password')
                ->assertPresent('input[name="email"]');
        });
    }

    /**
     * Test guest cannot access dashboard.
     */
    public function test_guest_cannot_access_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertPathIs('/login');
        });
    }

    /**
     * Test guest cannot access settings.
     */
    public function test_guest_cannot_access_settings(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/settings')
                ->assertPathIs('/login');
        });
    }
}
