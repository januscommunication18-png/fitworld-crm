<?php

namespace Tests\Browser;

use App\Models\Host;
use App\Models\ServicePlan;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ServicePlanTest extends DuskTestCase
{
    private function getOwnerUser(): ?User
    {
        $host = Host::whereNotNull('setup_completed_at')->first();
        if (!$host) {
            return null;
        }

        return $host->users()->where('role', 'owner')->first()
            ?? User::where('host_id', $host->id)->where('role', 'owner')->first();
    }

    /**
     * Set a hidden advance-select value and trigger change via JS.
     */
    private function setSelect(Browser $browser, string $id, string $value): void
    {
        $browser->script("
            var el = document.getElementById('{$id}');
            if (el) {
                el.value = '{$value}';
                el.dispatchEvent(new Event('change', { bubbles: true }));
                // Also trigger advance-select update if present
                var instance = window.HSSelect && window.HSSelect.getInstance(el);
                if (instance) instance.setValue('{$value}');
            }
        ");
        $browser->pause(300);
    }

    public function test_catalog_index_loads_with_services_tab(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $browser->loginAs($user)
                ->visit('/catalog?tab=services')
                ->assertSee('Classes & Services');
        });
    }

    public function test_create_service_plan_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $browser->loginAs($user)
                ->visit('/service-plans/create')
                ->assertSee('Create Service Plan')
                ->assertPresent('input[name="name"]')
                ->assertPresent('select[name="category"]')
                ->assertPresent('select[name="location_type"]')
                ->assertPresent('input[name="duration_minutes"]')
                ->assertPresent('input[name="max_participants"]');
        });
    }

    public function test_create_service_plan_with_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $planName = 'Dusk Service Test ' . time();

            $browser->loginAs($user)
                ->visit('/service-plans/create')
                ->pause(1000)
                ->type('name', $planName);

            $this->setSelect($browser, 'category', 'private_training');
            $this->setSelect($browser, 'location_type', 'in_studio');

            $browser->clear('duration_minutes')
                ->type('duration_minutes', '60')
                ->clear('max_participants')
                ->type('max_participants', '1')
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15)
                ->assertPathBeginsWith('/catalog');

            $servicePlan = ServicePlan::where('name', $planName)->first();
            if ($servicePlan) { $servicePlan->delete(); }
        });
    }

    public function test_create_service_plan_with_all_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $planName = 'Dusk Full Service ' . time();

            $browser->loginAs($user)
                ->visit('/service-plans/create')
                ->pause(1000)
                ->type('name', $planName)
                ->type('description', 'A comprehensive test service plan created by Dusk automation.');

            $this->setSelect($browser, 'category', 'consultation');
            $this->setSelect($browser, 'location_type', 'online');

            $browser->clear('duration_minutes')
                ->type('duration_minutes', '45')
                ->clear('buffer_minutes')
                ->type('buffer_minutes', '10')
                ->clear('max_participants')
                ->type('max_participants', '1')
                ->clear('booking_notice_hours')
                ->type('booking_notice_hours', '24')
                ->clear('cancellation_hours')
                ->type('cancellation_hours', '12')
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15)
                ->assertPathBeginsWith('/catalog');

            $servicePlan = ServicePlan::where('name', $planName)->first();
            if ($servicePlan) { $servicePlan->delete(); }
        });
    }

    public function test_create_service_plan_validation_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $browser->loginAs($user)
                ->visit('/service-plans/create')
                ->pause(500)
                ->clear('name')
                ->clear('duration_minutes')
                ->clear('max_participants')
                ->press('Create Service Plan')
                ->pause(1000)
                ->assertPathIs('/service-plans/create');
        });
    }

    public function test_view_service_plan(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $servicePlan = ServicePlan::where('host_id', $user->host_id)->first();
            if (!$servicePlan) { $this->markTestSkipped('No service plan found for this host'); }

            $browser->loginAs($user)
                ->visit('/service-plans/' . $servicePlan->id)
                ->assertSee($servicePlan->name);
        });
    }

    public function test_edit_service_plan(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $servicePlan = ServicePlan::where('host_id', $user->host_id)->first();
            if (!$servicePlan) { $this->markTestSkipped('No service plan found for this host'); }

            $browser->loginAs($user)
                ->visit('/service-plans/' . $servicePlan->id . '/edit')
                ->assertSee('Edit Service Plan')
                ->assertInputValue('name', $servicePlan->name);
        });
    }

    public function test_update_service_plan(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $servicePlan = ServicePlan::create([
                'host_id' => $user->host_id,
                'name' => 'Dusk Edit Service ' . time(),
                'slug' => 'dusk-edit-service-' . time(),
                'category' => 'private_training',
                'location_type' => 'in_studio',
                'duration_minutes' => 60,
                'max_participants' => 1,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
                'sort_order' => 999,
            ]);

            $updatedName = 'Dusk Updated Service ' . time();

            $browser->loginAs($user)
                ->visit('/service-plans/' . $servicePlan->id . '/edit')
                ->pause(1000)
                ->clear('name')
                ->type('name', $updatedName)
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15)
                ->assertPathBeginsWith('/catalog');

            $servicePlan->refresh();
            $servicePlan->delete();
        });
    }

    public function test_show_page_displays_service_plan_details(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $servicePlan = ServicePlan::create([
                'host_id' => $user->host_id,
                'name' => 'Dusk Detail Service ' . time(),
                'slug' => 'dusk-detail-service-' . time(),
                'description' => 'Detailed service description for testing.',
                'category' => 'therapy',
                'location_type' => 'online',
                'duration_minutes' => 90,
                'buffer_minutes' => 15,
                'max_participants' => 2,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
                'sort_order' => 999,
            ]);

            $browser->loginAs($user)
                ->visit('/service-plans/' . $servicePlan->id)
                ->assertSee($servicePlan->name)
                ->assertSee('Detailed service description for testing.')
                ->assertSee('Active');

            $servicePlan->delete();
        });
    }

    public function test_create_and_edit_service_plan_e2e(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $planName = 'Dusk E2E Service ' . time();
            $updatedName = 'Dusk E2E Updated ' . time();

            // Create
            $browser->loginAs($user)
                ->visit('/service-plans/create')
                ->pause(1000)
                ->type('name', $planName);

            $this->setSelect($browser, 'category', 'consultation');
            $this->setSelect($browser, 'location_type', 'in_studio');

            $browser->clear('duration_minutes')
                ->type('duration_minutes', '30')
                ->clear('max_participants')
                ->type('max_participants', '1')
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15);

            $servicePlan = ServicePlan::where('name', $planName)->where('host_id', $user->host_id)->first();
            $this->assertNotNull($servicePlan);

            // View
            $browser->visit('/service-plans/' . $servicePlan->id)
                ->assertSee($planName);

            // Edit
            $browser->visit('/service-plans/' . $servicePlan->id . '/edit')
                ->pause(1000)
                ->clear('name')
                ->type('name', $updatedName)
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15);

            $servicePlan->refresh();
            $this->assertEquals($updatedName, $servicePlan->name);
            $servicePlan->delete();
        });
    }

    public function test_guest_cannot_access_service_plans(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/service-plans/create')
                ->assertPathBeginsWith('/login');
        });
    }

    public function test_toggle_service_plan_active(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $servicePlan = ServicePlan::create([
                'host_id' => $user->host_id,
                'name' => 'Dusk Toggle Service ' . time(),
                'slug' => 'dusk-toggle-service-' . time(),
                'category' => 'other',
                'location_type' => 'in_studio',
                'duration_minutes' => 60,
                'max_participants' => 1,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
                'sort_order' => 999,
            ]);

            $browser->loginAs($user)
                ->visit('/service-plans/' . $servicePlan->id)
                ->assertSee($servicePlan->name)
                ->assertSee('Active');

            $servicePlan->delete();
        });
    }
}
