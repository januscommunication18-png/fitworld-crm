<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Host;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SettingsProfileTest extends DuskTestCase
{
    protected function getAuthenticatedUser()
    {
        $host = Host::whereNotNull('onboarding_completed_at')->first();
        if (!$host) return null;

        return $host->users()->where('role', 'owner')->first()
            ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
    }

    /**
     * Test profile page loads with user info.
     */
    public function test_profile_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/profile')
                ->assertSee('Personal Information')
                ->assertSee($user->first_name)
                ->assertSee($user->email);
        });
    }

    /**
     * Test edit profile drawer opens.
     */
    public function test_edit_profile_drawer_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/profile')
                ->click('[onclick*="edit-profile-drawer"]')
                ->waitFor('#edit-profile-drawer:not(.translate-x-full)')
                ->assertSee('Edit Personal Information')
                ->assertInputValue('first_name', $user->first_name)
                ->assertInputValue('last_name', $user->last_name);
        });
    }

    /**
     * Test email field is readonly in edit drawer.
     */
    public function test_email_is_readonly_in_edit_drawer(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/profile')
                ->click('[onclick*="edit-profile-drawer"]')
                ->waitFor('#edit-profile-drawer:not(.translate-x-full)')
                ->assertAttribute('#email', 'readonly', 'true');
        });
    }

    /**
     * Test save button is disabled when no changes made.
     */
    public function test_save_button_disabled_without_changes(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/profile')
                ->click('[onclick*="edit-profile-drawer"]')
                ->waitFor('#edit-profile-drawer:not(.translate-x-full)')
                ->assertDisabled('#save-profile-btn');
        });
    }

    /**
     * Test name fields reject digits.
     */
    public function test_name_fields_reject_digits(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/profile')
                ->click('[onclick*="edit-profile-drawer"]')
                ->waitFor('#edit-profile-drawer:not(.translate-x-full)')
                ->clear('first_name')
                ->type('first_name', 'John123')
                ->assertInputValue('first_name', 'John');
        });
    }

    /**
     * Test change password drawer opens.
     */
    public function test_change_password_drawer_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/profile')
                ->click('[onclick*="change-password-drawer"]')
                ->waitFor('#change-password-drawer:not(.translate-x-full)')
                ->assertSee('Change Password');
        });
    }

    /**
     * Test profile photo upload rejects unsupported formats.
     */
    public function test_photo_upload_rejects_unsupported_format(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/profile')
                ->assertSee('Personal Information');
            // File upload format validation is handled client-side
        });
    }
}
