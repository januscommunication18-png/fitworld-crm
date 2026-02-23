<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientScore;
use App\Models\ScoreEvent;
use App\Models\ScoringRule;
use App\Models\ScoringTier;
use App\Models\Host;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientScoringService
{
    protected Host $host;

    public function __construct(Host $host)
    {
        $this->host = $host;
    }

    /**
     * Record a scoring event for a client.
     */
    public function recordEvent(Client $client, string $eventType, array $metadata = []): ?ScoreEvent
    {
        $rule = ScoringRule::where('host_id', $this->host->id)
            ->where('event_type', $eventType)
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            // Fall back to default points
            $points = ScoreEvent::DEFAULT_EVENT_POINTS[$eventType] ?? 0;
        } else {
            $points = $rule->points;
        }

        if ($points === 0) {
            return null;
        }

        // Get current score before update
        $currentScore = $client->score?->engagement_score ?? 0;

        $event = ScoreEvent::create([
            'client_id' => $client->id,
            'host_id' => $this->host->id,
            'event_type' => $eventType,
            'points' => $points,
            'score_before' => $currentScore,
            'score_after' => $currentScore + $points,
            'description' => $metadata['description'] ?? null,
            'source_type' => $metadata['source_type'] ?? null,
            'source_id' => $metadata['source_id'] ?? null,
        ]);

        // Update client score
        $this->updateClientScore($client);

        return $event;
    }

    /**
     * Calculate and update a client's engagement score.
     */
    public function updateClientScore(Client $client): ClientScore
    {
        $score = ClientScore::firstOrNew([
            'client_id' => $client->id,
            'host_id' => $this->host->id,
        ]);

        // Store previous score for comparison
        $previousScore = $score->engagement_score ?? 0;

        // Calculate component metrics
        $metrics = $this->calculateMetrics($client);

        // Calculate total engagement score (sum of all components, capped at 1000)
        $totalScore = min(1000,
            $metrics['attendance'] +
            $metrics['spending'] +
            $metrics['engagement'] +
            $metrics['loyalty']
        );

        // Update score record
        $score->fill([
            'engagement_score' => $totalScore,
            'attendance_score' => $metrics['attendance'],
            'spending_score' => $metrics['spending'],
            'engagement_score_component' => $metrics['engagement'],
            'loyalty_score' => $metrics['loyalty'],
            'total_classes_30d' => $metrics['total_classes_30d'],
            'total_no_shows_30d' => $metrics['total_no_shows_30d'],
            'total_late_cancels_30d' => $metrics['total_late_cancels_30d'],
            'total_referrals' => $metrics['total_referrals'],
            'membership_renewals' => $metrics['membership_renewals'],
            'days_since_last_visit' => $metrics['days_since_last_visit'],
            'previous_score' => $previousScore,
            'score_calculated_at' => now(),
        ]);

        // Determine tier
        $tier = ScoringTier::getTierForScore($this->host->id, $totalScore);
        if ($tier) {
            $score->loyalty_tier = strtolower($tier->name);
        } else {
            $score->loyalty_tier = ClientScore::calculateTier($totalScore);
        }

        $score->save();

        return $score;
    }

    /**
     * Calculate component metric scores.
     */
    protected function calculateMetrics(Client $client): array
    {
        $now = now();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $ninetyDaysAgo = $now->copy()->subDays(90);

        // Count class attendance in last 30 days
        $totalClasses30d = $client->bookings()
            ->where('status', 'completed')
            ->where('start_time', '>=', $thirtyDaysAgo)
            ->count();

        // Count no-shows in last 30 days
        $noShows30d = $client->bookings()
            ->where('status', 'no_show')
            ->where('start_time', '>=', $thirtyDaysAgo)
            ->count();

        // Count late cancellations in last 30 days
        $lateCancels30d = $client->bookings()
            ->where('status', 'cancelled')
            ->where('cancelled_at', '>=', $thirtyDaysAgo)
            ->whereRaw('TIMESTAMPDIFF(HOUR, cancelled_at, start_time) < 24')
            ->count();

        // Get referrals count
        $referrals = $client->referrals ?? 0;

        // Get membership renewals
        $membershipRenewals = 0; // Would come from membership history

        // Calculate days since last visit
        $lastVisit = $client->bookings()
            ->where('status', 'completed')
            ->orderBy('start_time', 'desc')
            ->first();
        $daysSinceLastVisit = $lastVisit
            ? $lastVisit->start_time->diffInDays($now)
            : null;

        // Attendance Score (0-250): Based on classes attended
        $attendanceScore = min(250, $totalClasses30d * 25);
        // Subtract for no-shows and late cancels
        $attendanceScore = max(0, $attendanceScore - ($noShows30d * 30) - ($lateCancels30d * 15));

        // Spending Score (0-250): Based on total revenue
        $totalSpend = $client->transactions()
            ->where('status', 'completed')
            ->where('created_at', '>=', $ninetyDaysAgo)
            ->sum('amount') / 100; // Convert cents to dollars
        $spendingScore = min(250, (int) ($totalSpend / 5)); // $5 = 1 point

        // Engagement Score (0-250): Based on recency and frequency
        $engagementScore = 0;
        if ($daysSinceLastVisit !== null) {
            if ($daysSinceLastVisit <= 7) {
                $engagementScore = 200;
            } elseif ($daysSinceLastVisit <= 14) {
                $engagementScore = 150;
            } elseif ($daysSinceLastVisit <= 30) {
                $engagementScore = 100;
            } elseif ($daysSinceLastVisit <= 60) {
                $engagementScore = 50;
            } else {
                $engagementScore = 10;
            }
        }
        // Add frequency bonus
        $avgPerMonth = $totalClasses30d;
        $engagementScore = min(250, $engagementScore + ($avgPerMonth * 10));

        // Loyalty Score (0-250): Based on membership and duration
        $loyaltyScore = 0;
        if ($client->status === 'member') {
            $memberSince = $client->created_at;
            $monthsAsMember = $memberSince->diffInMonths($now);
            $loyaltyScore = min(200, 50 + ($monthsAsMember * 5));
            // Add referral bonus
            $loyaltyScore = min(250, $loyaltyScore + ($referrals * 15));
        }

        return [
            'attendance' => $attendanceScore,
            'spending' => $spendingScore,
            'engagement' => $engagementScore,
            'loyalty' => $loyaltyScore,
            'total_classes_30d' => $totalClasses30d,
            'total_no_shows_30d' => $noShows30d,
            'total_late_cancels_30d' => $lateCancels30d,
            'total_referrals' => $referrals,
            'membership_renewals' => $membershipRenewals,
            'days_since_last_visit' => $daysSinceLastVisit,
        ];
    }

    /**
     * Recalculate scores for all clients.
     */
    public function recalculateAllScores(): int
    {
        $clients = Client::where('host_id', $this->host->id)->get();
        $count = 0;

        foreach ($clients as $client) {
            $this->updateClientScore($client);
            $count++;
        }

        return $count;
    }

    /**
     * Get clients by tier.
     */
    public function getClientsByTier(string $tier)
    {
        return Client::where('host_id', $this->host->id)
            ->whereHas('score', function ($query) use ($tier) {
                $query->where('loyalty_tier', $tier);
            })
            ->get();
    }

    /**
     * Get top engaged clients.
     */
    public function getTopEngagedClients(int $limit = 10)
    {
        return Client::where('host_id', $this->host->id)
            ->whereHas('score')
            ->with('score')
            ->get()
            ->sortByDesc(fn($client) => $client->score->engagement_score ?? 0)
            ->take($limit);
    }

    /**
     * Get at-risk clients (declining engagement).
     */
    public function getAtRiskClients()
    {
        return Client::where('host_id', $this->host->id)
            ->whereHas('score', function ($query) {
                $query->where('days_since_last_visit', '>', 30)
                    ->where('engagement_score', '>', 100); // Was engaged, now declining
            })
            ->with('score')
            ->get();
    }

    /**
     * Calculate engagement trend for a client.
     */
    public function calculateTrend(Client $client): string
    {
        $score = $client->score;
        if (!$score) {
            return 'stable';
        }

        $change = $score->engagement_score - $score->previous_score;

        if ($change > 50) {
            return 'up';
        } elseif ($change < -50) {
            return 'down';
        }

        return 'stable';
    }

    /**
     * Award bonus points to a client.
     */
    public function awardBonusPoints(Client $client, int $points, string $reason): ScoreEvent
    {
        $currentScore = $client->score?->engagement_score ?? 0;

        $event = ScoreEvent::create([
            'client_id' => $client->id,
            'host_id' => $this->host->id,
            'event_type' => 'bonus_points',
            'points' => $points,
            'score_before' => $currentScore,
            'score_after' => $currentScore + $points,
            'description' => $reason,
        ]);

        $this->updateClientScore($client);

        return $event;
    }

    /**
     * Initialize default scoring rules for a host.
     */
    public static function initializeDefaultRules(Host $host): void
    {
        ScoringRule::createDefaultRules($host->id);
        ScoringTier::createDefaultTiers($host->id);
    }

    /**
     * Get scoring statistics for the host.
     */
    public function getStatistics(): array
    {
        $scores = ClientScore::where('host_id', $this->host->id)->get();

        return [
            'total_scored_clients' => $scores->count(),
            'average_score' => (int) ($scores->avg('engagement_score') ?? 0),
            'tier_breakdown' => [
                'vip' => $scores->where('loyalty_tier', 'vip')->count(),
                'gold' => $scores->where('loyalty_tier', 'gold')->count(),
                'silver' => $scores->where('loyalty_tier', 'silver')->count(),
                'bronze' => $scores->where('loyalty_tier', 'bronze')->count(),
            ],
            'score_distribution' => [
                'high' => $scores->where('engagement_score', '>=', 700)->count(),
                'medium' => $scores->filter(fn($s) => $s->engagement_score >= 400 && $s->engagement_score < 700)->count(),
                'low' => $scores->where('engagement_score', '<', 400)->count(),
            ],
        ];
    }

    /**
     * Get clients needing attention (at risk of churning).
     */
    public function getClientsNeedingAttention(int $limit = 10)
    {
        return Client::where('host_id', $this->host->id)
            ->whereHas('score', function ($query) {
                $query->where(function ($q) {
                    // High value clients who haven't visited recently
                    $q->where('engagement_score', '>=', 400)
                        ->where('days_since_last_visit', '>', 14);
                })->orWhere(function ($q) {
                    // Score declining
                    $q->whereColumn('engagement_score', '<', 'previous_score')
                        ->whereRaw('(previous_score - engagement_score) > 100');
                });
            })
            ->with('score')
            ->limit($limit)
            ->get();
    }
}
