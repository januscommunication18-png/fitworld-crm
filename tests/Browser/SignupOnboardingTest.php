<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SignupOnboardingTest extends DuskTestCase
{
    /**
     * Test the signup page loads correctly.
     */
    public function test_signup_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/signup')
                ->assertSee('Welcome')
                ->assertPresent('#signup-app');
        });
    }

    /**
     * Test step 1 welcome screen shows and can proceed.
     */
    public function test_step1_welcome_shows_get_started_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/signup')
                ->waitForText('Welcome')
                ->assertSee('Get Started');
        });
    }
}
