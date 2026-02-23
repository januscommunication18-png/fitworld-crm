<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Host;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\Segment;
use App\Models\SegmentRule;
use App\Models\ClientScore;
use App\Models\ScoringRule;
use App\Models\ScoringTier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferSegmentTestSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first host (or create one if needed)
        $host = Host::first();
        if (!$host) {
            $this->command->error('No host found. Please create a host first.');
            return;
        }

        $user = User::where('host_id', $host->id)->first();

        $this->command->info("Creating test data for host: {$host->name}");

        // Create Scoring Rules & Tiers
        $this->createScoringRulesAndTiers($host);

        // Create Segments
        $segments = $this->createSegments($host, $user);

        // Create Offers
        $offers = $this->createOffers($host, $user, $segments);

        // Assign clients to segments and create scores
        $this->assignClientsToSegments($host, $segments);

        // Create sample redemptions
        $this->createSampleRedemptions($host, $offers);

        $this->command->info('Test data created successfully!');
        $this->command->newLine();
        $this->command->info('=== Testing Scenarios ===');
        $this->command->newLine();
        $this->printTestingScenarios();
    }

    protected function createScoringRulesAndTiers(Host $host): void
    {
        // Create default scoring rules
        ScoringRule::createDefaultRules($host->id);
        ScoringTier::createDefaultTiers($host->id);

        $this->command->info('✓ Created scoring rules and tiers');
    }

    protected function createSegments(Host $host, ?User $user): array
    {
        $segments = [];

        // 1. Static Segment - VIP Members (manually assigned)
        $segments['vip'] = Segment::firstOrCreate(
            ['host_id' => $host->id, 'slug' => 'vip-members'],
            [
                'name' => 'VIP Members',
                'description' => 'Hand-picked VIP members who receive exclusive benefits',
                'color' => '#8B5CF6',
                'type' => 'static',
                'is_active' => true,
            ]
        );

        // 2. Dynamic Segment - High Spenders
        $segments['high_spenders'] = Segment::firstOrCreate(
            ['host_id' => $host->id, 'slug' => 'high-spenders'],
            [
                'name' => 'High Spenders',
                'description' => 'Clients who have spent over $500 total',
                'color' => '#10B981',
                'type' => 'dynamic',
                'is_active' => true,
            ]
        );

        if ($segments['high_spenders']->wasRecentlyCreated) {
            SegmentRule::create([
                'segment_id' => $segments['high_spenders']->id,
                'group_index' => 0,
                'field' => 'total_spend',
                'operator' => 'greater_than',
                'value' => '500',
            ]);
        }

        // 3. Dynamic Segment - Inactive Members
        $segments['inactive'] = Segment::firstOrCreate(
            ['host_id' => $host->id, 'slug' => 'inactive-members'],
            [
                'name' => 'Inactive Members',
                'description' => 'Members who haven\'t visited in 30+ days',
                'color' => '#EF4444',
                'type' => 'dynamic',
                'is_active' => true,
            ]
        );

        if ($segments['inactive']->wasRecentlyCreated) {
            SegmentRule::create([
                'segment_id' => $segments['inactive']->id,
                'group_index' => 0,
                'field' => 'last_visit',
                'operator' => 'older_than',
                'relative_value' => 30,
                'relative_unit' => 'days',
            ]);

            SegmentRule::create([
                'segment_id' => $segments['inactive']->id,
                'group_index' => 0,
                'field' => 'status',
                'operator' => 'equals',
                'value' => 'member',
            ]);
        }

        // 4. Dynamic Segment - New Clients (last 14 days)
        $segments['new_clients'] = Segment::firstOrCreate(
            ['host_id' => $host->id, 'slug' => 'new-clients'],
            [
                'name' => 'New Clients',
                'description' => 'Clients who joined in the last 14 days',
                'color' => '#3B82F6',
                'type' => 'dynamic',
                'is_active' => true,
            ]
        );

        if ($segments['new_clients']->wasRecentlyCreated) {
            SegmentRule::create([
                'segment_id' => $segments['new_clients']->id,
                'group_index' => 0,
                'field' => 'created_at',
                'operator' => 'newer_than',
                'relative_value' => 14,
                'relative_unit' => 'days',
            ]);
        }

        // 5. Smart Segment - Gold Tier
        $segments['gold_tier'] = Segment::firstOrCreate(
            ['host_id' => $host->id, 'slug' => 'gold-tier'],
            [
                'name' => 'Gold Tier Members',
                'description' => 'Members with Gold loyalty tier (500-749 points)',
                'color' => '#F59E0B',
                'type' => 'smart',
                'tier' => 'gold',
                'min_score' => 500,
                'max_score' => 749,
                'is_active' => true,
            ]
        );

        // 6. Dynamic Segment - Frequent Visitors
        $segments['frequent'] = Segment::firstOrCreate(
            ['host_id' => $host->id, 'slug' => 'frequent-visitors'],
            [
                'name' => 'Frequent Visitors',
                'description' => 'Clients with 5+ visits in the last 30 days',
                'color' => '#06B6D4',
                'type' => 'dynamic',
                'is_active' => true,
            ]
        );

        if ($segments['frequent']->wasRecentlyCreated) {
            SegmentRule::create([
                'segment_id' => $segments['frequent']->id,
                'group_index' => 0,
                'field' => 'visits_last_30_days',
                'operator' => 'greater_than_or_equal',
                'value' => '5',
            ]);
        }

        $this->command->info('✓ Created 6 segments (1 static, 4 dynamic, 1 smart)');

        return $segments;
    }

    protected function createOffers(Host $host, ?User $user, array $segments): array
    {
        $offers = [];

        // 1. Active Percentage Discount - General
        $offers['summer_sale'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'SUMMER20'],
            [
                'name' => 'Summer Sale 20% Off',
                'description' => 'Get 20% off all classes this summer!',
                'status' => 'active',
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(30),
                'applies_to' => 'classes',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'target_audience' => 'all_members',
                'total_usage_limit' => 100,
                'per_member_limit' => 3,
                'require_code' => true,
                'show_on_invoice' => true,
                'invoice_line_text' => 'Summer Sale Discount',
                'created_by' => $user?->id,
            ]
        );

        // 2. Fixed Amount Discount - For VIP Segment
        $offers['vip_discount'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'VIP25'],
            [
                'name' => 'VIP $25 Credit',
                'description' => 'Exclusive $25 credit for our VIP members',
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays(90),
                'applies_to' => 'all',
                'discount_type' => 'fixed_amount',
                'discount_value' => 25,
                'target_audience' => 'specific_segment',
                'segment_id' => $segments['vip']->id,
                'per_member_limit' => 1,
                'auto_apply' => true,
                'require_code' => false,
                'show_on_invoice' => true,
                'created_by' => $user?->id,
            ]
        );

        // 3. Win-back Offer for Inactive Members
        $offers['winback'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'COMEBACK30'],
            [
                'name' => 'We Miss You - 30% Off',
                'description' => 'We miss you! Come back and enjoy 30% off your next class.',
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays(60),
                'applies_to' => 'classes',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'target_audience' => 'specific_segment',
                'segment_id' => $segments['inactive']->id,
                'per_member_limit' => 1,
                'require_code' => true,
                'show_on_invoice' => true,
                'internal_notes' => 'Win-back campaign for Q1',
                'created_by' => $user?->id,
            ]
        );

        // 4. New Member Welcome Offer
        $offers['welcome'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'WELCOME'],
            [
                'name' => 'Welcome Gift - First Class Free',
                'description' => 'Welcome! Your first class is on us.',
                'status' => 'active',
                'applies_to' => 'classes',
                'discount_type' => 'percentage',
                'discount_value' => 100,
                'target_audience' => 'new_members',
                'per_member_limit' => 1,
                'first_x_users' => 50,
                'require_code' => false,
                'auto_apply' => true,
                'show_on_invoice' => true,
                'invoice_line_text' => 'Welcome Gift - First Class Free',
                'created_by' => $user?->id,
            ]
        );

        // 5. Membership Discount
        $offers['membership'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'ANNUAL15'],
            [
                'name' => 'Annual Membership 15% Off',
                'description' => 'Save 15% when you sign up for an annual membership',
                'status' => 'active',
                'applies_to' => 'memberships',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'target_audience' => 'all_members',
                'total_usage_limit' => 25,
                'require_code' => true,
                'show_on_invoice' => true,
                'created_by' => $user?->id,
            ]
        );

        // 6. Draft Offer (not yet active)
        $offers['draft'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'BLACKFRI50'],
            [
                'name' => 'Black Friday Special',
                'description' => '50% off everything for Black Friday!',
                'status' => 'draft',
                'start_date' => now()->addMonths(2),
                'end_date' => now()->addMonths(2)->addDays(3),
                'applies_to' => 'all',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'target_audience' => 'all_members',
                'total_usage_limit' => 200,
                'require_code' => true,
                'internal_notes' => 'Needs marketing team approval before activation',
                'created_by' => $user?->id,
            ]
        );

        // 7. Paused Offer
        $offers['paused'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'SPRING10'],
            [
                'name' => 'Spring Promo',
                'description' => '10% off spring classes',
                'status' => 'paused',
                'applies_to' => 'classes',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'target_audience' => 'all_members',
                'require_code' => true,
                'internal_notes' => 'Paused due to high redemption rate',
                'created_by' => $user?->id,
            ]
        );

        // 8. Expired Offer
        $offers['expired'] = Offer::firstOrCreate(
            ['host_id' => $host->id, 'code' => 'NEWYEAR25'],
            [
                'name' => 'New Year Sale',
                'description' => '25% off to start the new year right!',
                'status' => 'expired',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->subMonth(),
                'applies_to' => 'all',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'target_audience' => 'all_members',
                'total_redemptions' => 45,
                'total_discount_given' => 1125.50,
                'total_revenue_generated' => 3376.50,
                'require_code' => true,
                'created_by' => $user?->id,
            ]
        );

        $this->command->info('✓ Created 8 offers (4 active, 1 draft, 1 paused, 1 expired)');

        return $offers;
    }

    protected function assignClientsToSegments(Host $host, array $segments): void
    {
        $clients = Client::where('host_id', $host->id)->get();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found to assign to segments');
            return;
        }

        $assigned = 0;

        foreach ($clients as $index => $client) {
            // Create engagement score for each client
            $score = rand(100, 950);
            ClientScore::updateOrCreate(
                ['client_id' => $client->id, 'host_id' => $host->id],
                [
                    'engagement_score' => $score,
                    'loyalty_tier' => ClientScore::calculateTier($score),
                    'attendance_score' => rand(50, 200),
                    'spending_score' => rand(30, 200),
                    'engagement_score_component' => rand(50, 200),
                    'loyalty_score' => rand(30, 150),
                    'total_classes_30d' => rand(0, 12),
                    'total_no_shows_30d' => rand(0, 2),
                    'total_late_cancels_30d' => rand(0, 3),
                    'days_since_last_visit' => rand(1, 60),
                    'score_calculated_at' => now(),
                ]
            );

            // Assign some clients to VIP segment (static)
            if ($index < 5) {
                $client->segments()->syncWithoutDetaching([
                    $segments['vip']->id => ['matched_at' => now()]
                ]);
                $assigned++;
            }
        }

        // Note: Dynamic segment sync skipped - rules reference computed fields
        // that require special query handling. Segments will auto-sync when
        // the refresh button is clicked in the UI with proper query builders.

        $this->command->info("✓ Assigned clients to segments and created engagement scores");
    }

    protected function createSampleRedemptions(Host $host, array $offers): void
    {
        $clients = Client::where('host_id', $host->id)->take(10)->get();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found to create redemptions');
            return;
        }

        $channels = ['online', 'front_desk', 'app'];
        $redemptionCount = 0;

        // Create redemptions for summer sale
        if (isset($offers['summer_sale'])) {
            foreach ($clients->take(5) as $client) {
                $originalPrice = rand(50, 150);
                $discountAmount = $originalPrice * 0.20;

                OfferRedemption::create([
                    'host_id' => $host->id,
                    'offer_id' => $offers['summer_sale']->id,
                    'client_id' => $client->id,
                    'original_price' => $originalPrice,
                    'discount_amount' => $discountAmount,
                    'final_price' => $originalPrice - $discountAmount,
                    'channel' => $channels[array_rand($channels)],
                    'promo_code_used' => 'SUMMER20',
                    'status' => 'completed',
                    'completed_at' => now()->subDays(rand(1, 7)),
                ]);
                $redemptionCount++;
            }

            // Update offer stats
            $offers['summer_sale']->update([
                'total_redemptions' => 5,
                'total_discount_given' => OfferRedemption::where('offer_id', $offers['summer_sale']->id)->sum('discount_amount'),
                'total_revenue_generated' => OfferRedemption::where('offer_id', $offers['summer_sale']->id)->sum('final_price'),
            ]);
        }

        // Create redemptions for welcome offer
        if (isset($offers['welcome'])) {
            foreach ($clients->take(3) as $client) {
                $originalPrice = rand(25, 40);

                OfferRedemption::create([
                    'host_id' => $host->id,
                    'offer_id' => $offers['welcome']->id,
                    'client_id' => $client->id,
                    'original_price' => $originalPrice,
                    'discount_amount' => $originalPrice,
                    'final_price' => 0,
                    'channel' => 'online',
                    'status' => 'completed',
                    'completed_at' => now()->subDays(rand(1, 14)),
                ]);
                $redemptionCount++;
            }

            $offers['welcome']->update([
                'total_redemptions' => 3,
                'total_discount_given' => OfferRedemption::where('offer_id', $offers['welcome']->id)->sum('discount_amount'),
                'new_members_acquired' => 3,
            ]);
        }

        $this->command->info("✓ Created {$redemptionCount} sample redemptions");
    }

    protected function printTestingScenarios(): void
    {
        $scenarios = <<<'SCENARIOS'
1. SEGMENTS MODULE (/segments)
   ─────────────────────────────
   □ View all segments on index page
   □ Check segment cards show member counts
   □ Filter by segment type (All/Static/Dynamic/Smart)
   □ Create a new static segment
   □ Create a dynamic segment with rules:
     - Add rule: Status = Member
     - Add rule: Total Spend > 100
   □ View segment details and member list
   □ Add/remove clients from static segment
   □ Refresh dynamic segment membership
   □ Edit segment name and color
   □ Delete a segment

2. OFFERS MODULE (/offers)
   ────────────────────────
   □ View all offers on index page
   □ Check stats cards (Total, Active, Redemptions, Revenue)
   □ Filter by status (All/Active/Draft/Paused/Expired)
   □ Create new percentage discount offer:
     - Name: "Test 10% Off"
     - Code: TEST10
     - Discount: 10%
     - Target: All Members
   □ Create segment-targeted offer:
     - Target: Specific Segment → VIP Members
   □ View offer details and redemption history
   □ Duplicate an existing offer
   □ Toggle offer status (Pause/Activate)
   □ Edit offer details
   □ Delete an offer

3. INTEGRATION TESTS
   ──────────────────
   □ Create segment → Create offer targeting that segment
   □ Check offer shows segment name in target column
   □ Verify redemption analytics update correctly

4. CLIENT SCORING
   ───────────────
   □ Check clients have engagement scores
   □ Verify tier badges (Bronze/Silver/Gold/VIP)
   □ Smart segments auto-populate based on score tiers

URLs to test:
  • Segments: /segments
  • Offers: /offers
  • Create Segment: /segments/create
  • Create Offer: /offers/create

SCENARIOS;

        $this->command->line($scenarios);
    }
}
