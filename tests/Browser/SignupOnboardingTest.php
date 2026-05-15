<?php

namespace Tests\Browser;

use App\Models\Host;
use App\Models\User;
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
            $browser->logout()
                ->visit('/signup')
                ->waitFor('#signup-app', 10)
                ->assertPresent('#signup-app');
        });
    }

    /**
     * Test step 1 welcome screen shows and has get started button.
     */
    public function test_step1_welcome_shows_get_started_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Welcome to FITStudioHQ', 10)
                ->assertSee('Welcome to FITStudioHQ')
                ->assertSee('Get Started (Free)');
        });
    }

    /**
     * Test step 1 has a login link for existing users.
     */
    public function test_step1_has_login_link(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Already have an account?', 10)
                ->assertSee('Already have an account?')
                ->assertSeeLink('Log in');
        });
    }

    /**
     * Test clicking Get Started advances to step 2 (account creation).
     */
    public function test_step1_advances_to_step2(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Get Started (Free)', 10)
                ->press('Get Started (Free)')
                ->waitForText('Create your account', 10)
                ->assertSee('Create your account')
                ->assertSee("Let's start with the basics.");
        });
    }

    /**
     * Test step 2 account form has all required fields.
     */
    public function test_step2_account_form_has_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Get Started (Free)', 10)
                ->press('Get Started (Free)')
                ->waitForText('Create your account', 10)
                ->assertPresent('#first_name')
                ->assertPresent('#last_name')
                ->assertPresent('#email')
                ->assertPresent('#password');
        });
    }

    /**
     * Test step 2 back button returns to step 1.
     */
    public function test_step2_back_button_returns_to_step1(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Get Started (Free)', 10)
                ->press('Get Started (Free)')
                ->waitForText('Create your account', 10)
                ->press('Back')
                ->waitForText('Welcome to FITStudioHQ', 10)
                ->assertSee('Welcome to FITStudioHQ');
        });
    }

    /**
     * Test step 2 continue button is disabled without valid input.
     */
    public function test_step2_continue_disabled_without_valid_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Get Started (Free)', 10)
                ->press('Get Started (Free)')
                ->waitForText('Create your account', 10)
                ->assertAttribute('button[type="submit"]', 'disabled', 'true');
        });
    }

    /**
     * Test full signup flow: step 1 -> step 2 (register) -> step 3 (email verification).
     */
    public function test_signup_step1_to_step3_flow(): void
    {
        $email = 'dusk-test-' . time() . '@example.com';

        $this->browse(function (Browser $browser) use ($email) {
            // Step 1: Welcome
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Get Started (Free)', 10)
                ->press('Get Started (Free)')
                ->waitForText('Create your account', 10);

            // Step 2: Fill account form
            $browser->type('#first_name', 'Dusk')
                ->type('#last_name', 'Tester')
                ->type('#email', $email)
                ->type('#password', 'TestPass1!')
                ->pause(500); // Wait for validation to enable button

            // Check if legal checkbox exists and click it
            $browser->script("
                var checkbox = document.querySelector('input[type=\"checkbox\"]');
                if (checkbox && !checkbox.checked) checkbox.click();
            ");

            $browser->pause(300)
                ->press('Continue')
                ->waitForText('Check your email', 15)
                ->assertSee('Check your email')
                ->assertSee($email);
        });

        // Clean up: delete the test user and host
        $user = User::where('email', $email)->first();
        if ($user) {
            $host = Host::find($user->host_id);
            $user->tokens()->delete();
            $user->delete();
            if ($host) {
                $host->delete();
            }
        }
    }

    /**
     * Test step 3 email verification shows continue and resend buttons.
     */
    public function test_step3_email_verification_has_continue_and_resend(): void
    {
        $email = 'dusk-verify-' . time() . '@example.com';

        $this->browse(function (Browser $browser) use ($email) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Get Started (Free)', 10)
                ->press('Get Started (Free)')
                ->waitForText('Create your account', 10)
                ->type('#first_name', 'Dusk')
                ->type('#last_name', 'Verify')
                ->type('#email', $email)
                ->type('#password', 'TestPass1!')
                ->pause(500);

            $browser->script("
                var checkbox = document.querySelector('input[type=\"checkbox\"]');
                if (checkbox && !checkbox.checked) checkbox.click();
            ");

            $browser->pause(300)
                ->press('Continue')
                ->waitForText('Check your email', 15)
                ->assertSee('Continue Setup')
                ->assertSee('Resend verification email');
        });

        // Clean up
        $user = User::where('email', $email)->first();
        if ($user) {
            $host = Host::find($user->host_id);
            $user->tokens()->delete();
            $user->delete();
            if ($host) {
                $host->delete();
            }
        }
    }

    /**
     * Test step 3 continue advances to step 4 (studio basics).
     */
    public function test_step3_continues_to_step4_studio_basics(): void
    {
        $email = 'dusk-studio-' . time() . '@example.com';

        $this->browse(function (Browser $browser) use ($email) {
            $browser->logout()
                ->visit('/signup')
                ->waitForText('Get Started (Free)', 10)
                ->press('Get Started (Free)')
                ->waitForText('Create your account', 10)
                ->type('#first_name', 'Dusk')
                ->type('#last_name', 'Studio')
                ->type('#email', $email)
                ->type('#password', 'TestPass1!')
                ->pause(500);

            $browser->script("
                var checkbox = document.querySelector('input[type=\"checkbox\"]');
                if (checkbox && !checkbox.checked) checkbox.click();
            ");

            $browser->pause(300)
                ->press('Continue')
                ->waitForText('Check your email', 15)
                ->press('Continue Setup')
                ->waitForText('Tell us about your studio', 15)
                ->assertSee('Tell us about your studio')
                ->assertPresent('#studio_name')
                ->assertPresent('#subdomain');
        });

        // Clean up
        $user = User::where('email', $email)->first();
        if ($user) {
            $host = Host::find($user->host_id);
            $user->tokens()->delete();
            $user->delete();
            if ($host) {
                $host->delete();
            }
        }
    }

    /**
     * Test step 4 studio basics form has all required fields.
     */
    public function test_step4_studio_basics_has_required_fields(): void
    {
        $email = 'dusk-fields-' . time() . '@example.com';

        $this->browse(function (Browser $browser) use ($email) {
            $this->navigateToStep4($browser, $email);

            $browser->assertSee('Studio Name')
                ->assertSee('Studio Categories')
                ->assertSee('Studio Address')
                ->assertSee('Timezone')
                ->assertSee('Default Currency')
                ->assertSee('Your Studio URL');
        });

        $this->cleanupTestUser($email);
    }

    /**
     * Test step 4 subdomain auto-generates from studio name.
     */
    public function test_step4_subdomain_autogenerates_from_studio_name(): void
    {
        $email = 'dusk-subdomain-' . time() . '@example.com';

        $this->browse(function (Browser $browser) use ($email) {
            $this->navigateToStep4($browser, $email);

            $browser->type('#studio_name', 'My Test Studio')
                ->pause(1000) // Wait for subdomain to auto-generate and check availability
                ->assertInputValue('#subdomain', 'my-test-studio');
        });

        $this->cleanupTestUser($email);
    }

    /**
     * Test authenticated user with completed onboarding is redirected from signup to dashboard.
     */
    public function test_authenticated_user_redirected_from_signup(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNotNull('setup_completed_at')->first();
            if (!$host) {
                $this->markTestSkipped('No host with completed setup found');
            }

            $user = $host->users()->where('role', 'owner')->first()
                ?? User::where('host_id', $host->id)->where('role', 'owner')->first();

            if (!$user) {
                $this->markTestSkipped('No owner user found');
            }

            $browser->loginAs($user)
                ->visit('/signup')
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * Test get-started page loads for owner with incomplete setup.
     */
    public function test_get_started_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $host = Host::whereNotNull('onboarding_completed_at')
                ->whereNull('setup_completed_at')
                ->first();

            if (!$host) {
                $this->markTestSkipped('No host with completed onboarding but incomplete setup found');
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
     * Test guest cannot access get-started page.
     */
    public function test_guest_cannot_access_get_started(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/get-started')
                ->assertPathIs('/login');
        });
    }

    /**
     * Helper: Navigate through steps 1-3 to reach step 4.
     */
    private function navigateToStep4(Browser $browser, string $email): void
    {
        $browser->logout()
            ->visit('/signup')
            ->waitForText('Get Started (Free)', 10)
            ->press('Get Started (Free)')
            ->waitForText('Create your account', 10)
            ->type('#first_name', 'Dusk')
            ->type('#last_name', 'Helper')
            ->type('#email', $email)
            ->type('#password', 'TestPass1!')
            ->pause(500);

        $browser->script("
            var checkbox = document.querySelector('input[type=\"checkbox\"]');
            if (checkbox && !checkbox.checked) checkbox.click();
        ");

        $browser->pause(300)
            ->press('Continue')
            ->waitForText('Check your email', 15)
            ->press('Continue Setup')
            ->waitForText('Tell us about your studio', 15);
    }

    /**
     * Helper: Clean up test user and host created during signup.
     */
    private function cleanupTestUser(string $email): void
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $host = Host::find($user->host_id);
            $user->tokens()->delete();
            $user->delete();
            if ($host) {
                $host->delete();
            }
        }
    }
}
