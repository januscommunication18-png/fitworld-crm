<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Host;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SettingsStudioProfileTest extends DuskTestCase
{
    protected function getAuthenticatedUser()
    {
        $host = Host::whereNotNull('onboarding_completed_at')->first();
        if (!$host) return null;

        return $host->users()->where('role', 'owner')->first()
            ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
    }

    /**
     * Test studio profile page loads.
     */
    public function test_studio_profile_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->assertSee('Basic Information');
        });
    }

    /**
     * Test edit basic info drawer opens.
     */
    public function test_edit_basic_info_drawer_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->click('[onclick*="edit-basic-drawer"]')
                ->waitFor('#edit-basic-drawer:not(.translate-x-full)')
                ->assertSee('Edit Basic Information');
        });
    }

    /**
     * Test subdomain is readonly in edit basic drawer.
     */
    public function test_subdomain_is_readonly(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $host = $user->host;
            if (!$host->subdomain) $this->markTestSkipped('No subdomain set');

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->click('[onclick*="edit-basic-drawer"]')
                ->waitFor('#edit-basic-drawer:not(.translate-x-full)')
                ->assertAttribute('#subdomain', 'readonly', 'true');
        });
    }

    /**
     * Test edit contact drawer opens and has phone masking.
     */
    public function test_edit_contact_drawer_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->click('[onclick*="edit-contact-drawer"]')
                ->waitFor('#edit-contact-drawer:not(.translate-x-full)')
                ->assertSee('Edit Contact Information')
                ->assertPresent('#phone_country_contact_drawer');
        });
    }

    /**
     * Test contact name rejects digits.
     */
    public function test_contact_name_rejects_digits(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->click('[onclick*="edit-contact-drawer"]')
                ->waitFor('#edit-contact-drawer:not(.translate-x-full)')
                ->clear('contact_name')
                ->type('contact_name', 'Jane123')
                ->assertInputValueIsNot('contact_name', 'Jane123');
        });
    }

    /**
     * Test drawer closes when clicking backdrop.
     */
    public function test_drawer_closes_on_backdrop_click(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->click('[onclick*="edit-basic-drawer"]')
                ->waitFor('#edit-basic-drawer:not(.translate-x-full)')
                ->click('#drawer-backdrop')
                ->pause(400)
                ->assertPresent('#edit-basic-drawer.translate-x-full');
        });
    }

    /**
     * Test required settings card hidden when all complete.
     */
    public function test_required_settings_card_visibility(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $host = $user->host;
            $allComplete = !empty($host->studio_name)
                && !empty($host->studio_structure)
                && !empty($host->subdomain)
                && !empty($host->studio_categories)
                && !empty($host->default_language_app)
                && !empty($host->default_currency)
                && isset($host->booking_settings['allow_cancellations']);

            $browser->loginAs($user)
                ->visit('/settings/studio/profile');

            if ($allComplete) {
                $browser->assertDontSee('Complete These Required Settings');
            } else {
                $browser->assertSee('Complete These Required Settings');
            }
        });
    }

    /**
     * Test studio logo upload drawer opens.
     */
    public function test_upload_logo_drawer_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->click('[onclick*="upload-logo-drawer"]')
                ->waitFor('#upload-logo-drawer:not(.translate-x-full)')
                ->assertSee('Upload Studio Logo');
        });
    }

    /**
     * Test gallery delete shows confirmation.
     */
    public function test_gallery_delete_shows_confirmation(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getAuthenticatedUser();
            if (!$user) $this->markTestSkipped('No user found');

            $host = $user->host;
            if ($host->galleryImages()->count() === 0) {
                $this->markTestSkipped('No gallery images');
            }

            $browser->loginAs($user)
                ->visit('/settings/studio/profile')
                ->click('.gallery-item button[onclick*="deleteGalleryImage"]')
                ->waitFor('#confirmModal:not(.hidden)')
                ->assertSee('Delete Image');
        });
    }
}
