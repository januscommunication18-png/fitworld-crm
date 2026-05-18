<?php

namespace Tests\Browser;

use App\Models\ClassPass;
use App\Models\Host;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ClassPassTest extends DuskTestCase
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
                var instance = window.HSSelect && window.HSSelect.getInstance(el);
                if (instance) instance.setValue('{$value}');
            }
        ");
        $browser->pause(300);
    }

    public function test_catalog_index_loads_with_class_passes_tab(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $browser->loginAs($user)
                ->visit('/catalog?tab=class-passes')
                ->assertSee('Classes & Services');
        });
    }

    public function test_create_class_pass_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $browser->loginAs($user)
                ->visit('/class-passes/create')
                ->assertSee('Create Class Pass')
                ->assertPresent('input[name="name"]')
                ->assertPresent('input[name="class_count"]')
                ->assertPresent('input[name="default_credits_per_class"]')
                ->assertPresent('select[name="validity_type"]')
                ->assertPresent('select[name="activation_type"]');
        });
    }

    public function test_create_class_pass_with_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $host = $user->host;
            $defaultCurrency = $host->default_currency ?? 'USD';
            $passName = 'Dusk Pass Test ' . time();

            $browser->loginAs($user)
                ->visit('/class-passes/create')
                ->pause(1000)
                ->type('name', $passName)
                ->clear('class_count')
                ->type('class_count', '10')
                ->clear('default_credits_per_class')
                ->type('default_credits_per_class', '1')
                ->type("prices[{$defaultCurrency}]", '99.99')
                ->select('validity_type', 'days')
                ->clear('validity_value')
                ->type('validity_value', '30')
                ->select('activation_type', 'on_purchase');

            $this->setSelect($browser, 'eligibility_type', 'all');

            $browser->select('status', 'active')
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15)
                ->assertPathBeginsWith('/catalog');

            $classPass = ClassPass::where('name', $passName)->first();
            if ($classPass) { $classPass->delete(); }
        });
    }

    public function test_create_class_pass_with_all_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $host = $user->host;
            $defaultCurrency = $host->default_currency ?? 'USD';
            $passName = 'Dusk Full Pass ' . time();

            $browser->loginAs($user)
                ->visit('/class-passes/create')
                ->pause(1000)
                ->type('name', $passName)
                ->type('description', 'A comprehensive test class pass.')
                ->clear('class_count')
                ->type('class_count', '20')
                ->clear('default_credits_per_class')
                ->type('default_credits_per_class', '2')
                ->type("prices[{$defaultCurrency}]", '149.99')
                ->type("new_member_prices[{$defaultCurrency}]", '129.99')
                ->select('validity_type', 'months')
                ->clear('validity_value')
                ->type('validity_value', '3')
                ->select('activation_type', 'on_first_booking');

            $this->setSelect($browser, 'eligibility_type', 'all');

            $browser->clear('cancellation_grace_hours')
                ->type('cancellation_grace_hours', '24')
                ->select('status', 'active')
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15)
                ->assertPathBeginsWith('/catalog');

            $classPass = ClassPass::where('name', $passName)->first();
            $this->assertNotNull($classPass);
            $this->assertEquals(20, $classPass->class_count);
            $this->assertEquals('months', $classPass->validity_type);

            if ($classPass) { $classPass->delete(); }
        });
    }

    public function test_create_class_pass_validation_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $browser->loginAs($user)
                ->visit('/class-passes/create')
                ->pause(500)
                ->clear('name')
                ->clear('class_count')
                ->clear('default_credits_per_class')
                ->press('Create Class Pass')
                ->pause(1000)
                ->assertPathIs('/class-passes/create');
        });
    }

    public function test_view_class_pass(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $classPass = ClassPass::where('host_id', $user->host_id)->first();
            if (!$classPass) { $this->markTestSkipped('No class pass found for this host'); }

            $browser->loginAs($user)
                ->visit('/class-passes/' . $classPass->id)
                ->assertSee($classPass->name);
        });
    }

    public function test_edit_class_pass(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $classPass = ClassPass::where('host_id', $user->host_id)->first();
            if (!$classPass) { $this->markTestSkipped('No class pass found for this host'); }

            $browser->loginAs($user)
                ->visit('/class-passes/' . $classPass->id . '/edit')
                ->assertSee('Edit Class Pass')
                ->assertInputValue('name', $classPass->name);
        });
    }

    public function test_update_class_pass(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $host = $user->host;
            $defaultCurrency = $host->default_currency ?? 'USD';

            $classPass = ClassPass::create([
                'host_id' => $user->host_id,
                'name' => 'Dusk Edit Pass ' . time(),
                'class_count' => 10,
                'default_credits_per_class' => 1,
                'price' => 50,
                'prices' => [$defaultCurrency => 50],
                'validity_type' => 'days',
                'validity_value' => 30,
                'expires_after_days' => 30,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'status' => 'active',
                'visibility_public' => true,
                'sort_order' => 999,
            ]);

            $updatedName = 'Dusk Updated Pass ' . time();

            $browser->loginAs($user)
                ->visit('/class-passes/' . $classPass->id . '/edit')
                ->pause(1000)
                ->clear('name')
                ->type('name', $updatedName)
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15)
                ->assertPathBeginsWith('/catalog');

            $classPass->refresh();
            $this->assertEquals($updatedName, $classPass->name);
            $classPass->delete();
        });
    }

    public function test_show_page_displays_class_pass_details(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $host = $user->host;
            $defaultCurrency = $host->default_currency ?? 'USD';

            $classPass = ClassPass::create([
                'host_id' => $user->host_id,
                'name' => 'Dusk Detail Pass ' . time(),
                'description' => 'Detailed pass description for testing.',
                'class_count' => 15,
                'default_credits_per_class' => 1,
                'price' => 75,
                'prices' => [$defaultCurrency => 75],
                'validity_type' => 'months',
                'validity_value' => 6,
                'expires_after_days' => 180,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'registration_fee' => 10,
                'registration_fees' => [$defaultCurrency => 10],
                'cancellation_fee' => 5,
                'cancellation_fees' => [$defaultCurrency => 5],
                'cancellation_grace_hours' => 48,
                'status' => 'active',
                'visibility_public' => true,
                'sort_order' => 999,
            ]);

            $browser->loginAs($user)
                ->visit('/class-passes/' . $classPass->id)
                ->assertSee($classPass->name)
                ->assertSee('Detailed pass description for testing.')
                ->assertSee('15')
                ->assertSee('Active')
                ->assertSee('All Classes');

            $classPass->delete();
        });
    }

    public function test_create_and_edit_class_pass_e2e(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $host = $user->host;
            $defaultCurrency = $host->default_currency ?? 'USD';
            $passName = 'Dusk E2E Pass ' . time();
            $updatedName = 'Dusk E2E Updated ' . time();

            // Create
            $browser->loginAs($user)
                ->visit('/class-passes/create')
                ->pause(1000)
                ->type('name', $passName)
                ->clear('class_count')
                ->type('class_count', '5')
                ->clear('default_credits_per_class')
                ->type('default_credits_per_class', '1')
                ->type("prices[{$defaultCurrency}]", '49.99')
                ->select('validity_type', 'days')
                ->clear('validity_value')
                ->type('validity_value', '14')
                ->select('activation_type', 'on_purchase');

            $this->setSelect($browser, 'eligibility_type', 'all');

            $browser->select('status', 'active')
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15);

            $classPass = ClassPass::where('name', $passName)->where('host_id', $user->host_id)->first();
            $this->assertNotNull($classPass);

            // View
            $browser->visit('/class-passes/' . $classPass->id)
                ->assertSee($passName);

            // Edit
            $browser->visit('/class-passes/' . $classPass->id . '/edit')
                ->pause(1000)
                ->clear('name')
                ->type('name', $updatedName)
                ->clear('class_count')
                ->type('class_count', '10')
                ->pause(500)
                ->script("document.querySelector('form[data-validate]').submit()");

            $browser->waitForLocation('/catalog', 15);

            $classPass->refresh();
            $this->assertEquals($updatedName, $classPass->name);
            $this->assertEquals(10, $classPass->class_count);
            $classPass->delete();
        });
    }

    public function test_guest_cannot_access_class_passes(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/class-passes/create')
                ->assertPathBeginsWith('/login');
        });
    }

    public function test_guest_cannot_access_catalog_class_passes(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/catalog?tab=class-passes')
                ->assertPathBeginsWith('/login');
        });
    }

    public function test_class_pass_with_recurring_settings(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $host = $user->host;
            $defaultCurrency = $host->default_currency ?? 'USD';

            $classPass = ClassPass::create([
                'host_id' => $user->host_id,
                'name' => 'Dusk Recurring Pass ' . time(),
                'class_count' => 10,
                'default_credits_per_class' => 1,
                'price' => 99,
                'prices' => [$defaultCurrency => 99],
                'validity_type' => 'months',
                'validity_value' => 1,
                'expires_after_days' => 30,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'is_recurring' => true,
                'renewal_interval' => 'monthly',
                'rollover_enabled' => true,
                'max_rollover_credits' => 5,
                'max_rollover_periods' => 2,
                'status' => 'active',
                'visibility_public' => true,
                'sort_order' => 999,
            ]);

            $browser->loginAs($user)
                ->visit('/class-passes/' . $classPass->id)
                ->assertSee($classPass->name)
                ->assertSee('Recurring')
                ->assertSee('Auto-Renewal');

            $classPass->delete();
        });
    }

    public function test_class_pass_with_advanced_features(): void
    {
        $this->browse(function (Browser $browser) {
            $user = $this->getOwnerUser();
            if (!$user) { $this->markTestSkipped('No owner user with completed setup found'); }

            $host = $user->host;
            $defaultCurrency = $host->default_currency ?? 'USD';

            $classPass = ClassPass::create([
                'host_id' => $user->host_id,
                'name' => 'Dusk Advanced Pass ' . time(),
                'class_count' => 20,
                'default_credits_per_class' => 1,
                'price' => 199,
                'prices' => [$defaultCurrency => 199],
                'validity_type' => 'months',
                'validity_value' => 3,
                'expires_after_days' => 90,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'allow_admin_extension' => true,
                'allow_freeze' => true,
                'max_freeze_days' => 14,
                'allow_transfer' => true,
                'allow_family_sharing' => true,
                'max_family_members' => 3,
                'allow_gifting' => true,
                'status' => 'active',
                'visibility_public' => true,
                'sort_order' => 999,
            ]);

            $browser->loginAs($user)
                ->visit('/class-passes/' . $classPass->id)
                ->assertSee($classPass->name)
                ->assertSee('Admin Extension')
                ->assertSee('Freeze')
                ->assertSee('Transfer')
                ->assertSee('Family Sharing')
                ->assertSee('Gifting');

            $classPass->delete();
        });
    }
}
