<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ClientDetailResource;
use App\Http\Resources\Api\V1\ClientResource;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\ClientFieldDefinition;
use App\Models\ClientFieldSection;
use App\Models\ClientNote;
use App\Models\CustomerMembership;
use App\Models\ServiceSlot;
use App\Models\Tag;
use App\Rules\ValidName;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Mobile clients listing. Host resolved by `studio.context` middleware.
 */
class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $host = $request->attributes->get('currentHost');
        $user = $request->user();
        $search = trim((string) $request->get('search', ''));

        // Page size — client-controlled (e.g. 5 for testing), capped at 100.
        $perPage = (int) $request->get('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        // Users with `students.view_all` browse the full directory. Everyone
        // else (restricted) sees nothing until they search — they can look a
        // client up but can't browse the whole roster. Both groups search the
        // same way: by full name, first name, last name, email or phone.
        $restricted = ! $user->hasPermission('students.view_all', $host);

        if ($restricted && $search === '') {
            return ClientResource::collection(
                new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage)
            );
        }

        $query = Client::forHost($host->id)->active()->with('tags');

        if ($search !== '') {
            $query->search($search);
        }

        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }

        if ($request->filled('source')) {
            $query->withSource($request->source);
        }

        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        // Newest first by default, with id as a stable tiebreaker so a just-
        // created client always sorts to the top even on equal timestamps.
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection)->orderByDesc('id');

        return ClientResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    /**
     * Dropdown options + host tags + dynamic custom fields for the Add Client
     * form. Gated by `students.create`.
     */
    public function formOptions(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        abort_unless(
            $request->user()->hasPermission('students.create', $host),
            403,
            'You do not have permission to add clients.'
        );

        $sections = ClientFieldSection::forHost($host->id)->active()->ordered()
            ->with(['activeFieldDefinitions' => fn ($q) => $q->where('show_on_add', true)->ordered()])
            ->get();
        $unsectioned = ClientFieldDefinition::forHost($host->id)->active()
            ->withoutSection()->where('show_on_add', true)->ordered()->get();

        $customSections = $sections
            ->map(fn ($s) => [
                'name' => $s->name,
                'fields' => $s->activeFieldDefinitions->map(fn ($f) => $this->mapFieldDefinition($f))->values(),
            ])
            ->filter(fn ($s) => count($s['fields']) > 0)
            ->values();

        // Phone country selector — same data the host web `x-phone-input` uses.
        $opCountries = $host->operating_countries ?: [$host->country ?? 'US'];
        $defaultCountry = $host->country ?? ($opCountries[0] ?? 'US');

        return response()->json(['data' => [
            'genders' => $this->kvList(Client::getGenders()),
            'statuses' => $this->kvList(Client::getStatuses()),
            'lead_sources' => $this->kvList(Client::getLeadSources()),
            'experience_levels' => $this->kvList(Client::getExperienceLevels()),
            'contact_methods' => $this->kvList(Client::getContactMethods()),
            'membership_statuses' => $this->kvList(Client::getMembershipStatuses()),
            'tags' => Tag::forHost($host->id)->orderBy('name')->get(['id', 'name', 'color']),
            'phone_countries' => array_values($opCountries),
            'phone_default_country' => $defaultCountry,
            'custom_sections' => $customSections,
            'custom_unsectioned' => $unsectioned->map(fn ($f) => $this->mapFieldDefinition($f))->values(),
        ]]);
    }

    /**
     * Create a client. Mirrors the host web validation. Gated by
     * `students.create`.
     */
    public function store(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        abort_unless(
            $request->user()->hasPermission('students.create', $host),
            403,
            'You do not have permission to add clients.'
        );

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['required', 'string', 'max:50', new ValidName],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(array_keys(Client::getGenders()))],

            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],

            'status' => ['required', Rule::in(array_keys(Client::getStatuses()))],
            'membership_status' => ['nullable', Rule::in(array_keys(Client::getMembershipStatuses()))],
            'membership_start_date' => ['nullable', 'date'],
            'membership_end_date' => ['nullable', 'date'],
            'membership_renewal_date' => ['nullable', 'date'],

            'lead_source' => ['required', Rule::in(array_keys(Client::getLeadSources()))],
            'source_url' => ['nullable', 'string', 'max:500'],
            'referral_source' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],

            'email_opt_in' => ['nullable', 'boolean'],
            'sms_opt_in' => ['nullable', 'boolean'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'preferred_contact_method' => ['nullable', 'array'],
            'preferred_contact_method.*' => ['nullable', Rule::in(array_keys(Client::getContactMethods()))],

            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_email' => ['nullable', 'email', 'max:255'],

            'medical_conditions' => ['nullable', 'string'],
            'injuries' => ['nullable', 'string'],
            'limitations' => ['nullable', 'string'],
            'fitness_goals' => ['nullable', 'string'],
            'experience_level' => ['nullable', Rule::in(array_keys(Client::getExperienceLevels()))],
            'pregnancy_status' => ['nullable', 'boolean'],

            'notes' => ['nullable', 'string'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $client = Client::create([
            'host_id' => $host->id,
            'created_by_user_id' => Auth::id(),
            'created_via' => 'mobile',
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'secondary_phone' => $validated['secondary_phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state_province' => $validated['state_province'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,
            'status' => $validated['status'],
            'membership_status' => $validated['membership_status'] ?? Client::MEMBERSHIP_NONE,
            'membership_start_date' => $validated['membership_start_date'] ?? null,
            'membership_end_date' => $validated['membership_end_date'] ?? null,
            'membership_renewal_date' => $validated['membership_renewal_date'] ?? null,
            'lead_source' => $validated['lead_source'],
            'source_url' => $validated['source_url'] ?? null,
            'referral_source' => $validated['referral_source'] ?? null,
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_medium' => $validated['utm_medium'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'utm_term' => $validated['utm_term'] ?? null,
            'utm_content' => $validated['utm_content'] ?? null,
            'email_opt_in' => $validated['email_opt_in'] ?? true,
            'sms_opt_in' => $validated['sms_opt_in'] ?? false,
            'marketing_opt_in' => $validated['marketing_opt_in'] ?? true,
            'preferred_contact_method' => ! empty($validated['preferred_contact_method'])
                ? implode(',', $validated['preferred_contact_method'])
                : 'email',
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'emergency_contact_email' => $validated['emergency_contact_email'] ?? null,
            'medical_conditions' => $validated['medical_conditions'] ?? null,
            'injuries' => $validated['injuries'] ?? null,
            'limitations' => $validated['limitations'] ?? null,
            'fitness_goals' => $validated['fitness_goals'] ?? null,
            'experience_level' => $validated['experience_level'] ?? null,
            'pregnancy_status' => $validated['pregnancy_status'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['tags'])) {
            $client->tags()->attach($validated['tags']);
            Tag::whereIn('id', $validated['tags'])->each(fn ($tag) => $tag->updateUsageCount());
        }

        if (! empty($validated['custom_fields'])) {
            $this->saveCustomFieldValues($client, $validated['custom_fields']);
        }

        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => ClientNote::TYPE_SYSTEM,
            'content' => 'Client created',
        ]);

        return response()->json([
            'message' => 'Client created successfully.',
            'data' => new ClientResource($client->load('tags')),
        ], 201);
    }

    /** @return array<int, array{value: string, label: string}> */
    private function kvList(array $map): array
    {
        $out = [];
        foreach ($map as $value => $label) {
            $out[] = ['value' => (string) $value, 'label' => (string) $label];
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private function mapFieldDefinition(ClientFieldDefinition $f): array
    {
        $options = $f->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        return [
            'key' => $f->field_key,
            'label' => $f->field_label,
            'type' => $f->field_type,
            'required' => (bool) $f->is_required,
            'help_text' => $f->help_text,
            'default_value' => $f->default_value,
            'options' => is_array($options) ? array_values($options) : [],
        ];
    }

    private function saveCustomFieldValues(Client $client, array $customFields): void
    {
        foreach ($customFields as $fieldKey => $value) {
            $definition = ClientFieldDefinition::forHost($client->host_id)
                ->where('field_key', $fieldKey)
                ->first();

            if (! $definition) {
                continue;
            }

            if ($definition->field_type === ClientFieldDefinition::TYPE_CHECKBOX && is_array($value)) {
                $value = json_encode($value);
            }

            $client->fieldValues()->updateOrCreate(
                ['field_definition_id' => $definition->id],
                ['value' => $value]
            );
        }
    }

    /**
     * Full client profile for the mobile detail screen.
     */
    public function show(Request $request, int $id): ClientDetailResource
    {
        $host = $request->attributes->get('currentHost');

        $client = Client::forHost($host->id)
            ->with(['tags', 'fieldValues.fieldDefinition'])
            ->findOrFail($id);

        // All bookings (most recent first) — powers the Bookings + Activity tabs.
        $bookings = Booking::forClient($client->id)
            ->with(['bookable' => fn (MorphTo $m) => $m->morphWith([
                ClassSession::class => ['classPlan'],
            ])])
            ->latest('booked_at')
            ->limit(100)
            ->get();

        $client->bookings = $bookings;
        $client->recent_bookings = $bookings->take(10);

        // Status counts (one grouped query) + attendance (orthogonal to status).
        $statusCounts = Booking::forClient($client->id)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $client->booking_stats = [
            'total' => (int) $statusCounts->sum(),
            'confirmed' => (int) ($statusCounts['confirmed'] ?? 0),
            'attended' => Booking::forClient($client->id)->whereNotNull('checked_in_at')->count(),
            'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            'no_show' => (int) ($statusCounts['no_show'] ?? 0),
        ];

        // Category summary: classes / services / memberships / packages.
        $client->booking_summary = [
            'classes' => Booking::forClient($client->id)->where('bookable_type', ClassSession::class)->count(),
            'services' => Booking::forClient($client->id)->where('bookable_type', ServiceSlot::class)->count(),
            'memberships' => $client->customerMemberships()->count(),
            'packages' => $client->classPassPurchases()->count(),
        ];

        $client->active_membership = $client->customerMemberships()
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->with('membershipPlan')
            ->latest()
            ->first();

        $client->client_score = $this->calculateClientScore($client, $host, $client->booking_stats);

        // Notes timeline (newest first) with the author who wrote each.
        // NB: a separate attribute name — the model's `notes` column holds the
        // free-text internal note shown on the Overview tab.
        $client->notes_timeline = $client->clientNotes()
            ->with('author')
            ->latest()
            ->limit(50)
            ->get();

        // Questionnaire responses with the questionnaire name + linked booking.
        $client->questionnaires = $client->questionnaireResponses()
            ->with(['version.questionnaire', 'booking.bookable'])
            ->latest()
            ->limit(50)
            ->get();

        // Progress: body measurements + scored progress reports.
        $client->measurements = $client->measurements()
            ->with('recordedBy')
            ->orderByDesc('measured_at')
            ->limit(50)
            ->get();

        $client->progress_reports = $client->progressReports()
            ->with('template')
            ->limit(50)
            ->get();

        return new ClientDetailResource($client);
    }

    /**
     * Add a note (note/call/email) to a client's timeline.
     */
    public function storeNote(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $client = Client::forHost($host->id)->findOrFail($id);

        $validated = $request->validate([
            'note_type' => ['required', Rule::in(array_keys(ClientNote::getNoteTypes()))],
            'content' => ['required', 'string'],
        ]);

        $note = ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
        ])->load('author');

        return response()->json([
            'data' => [
                'id' => $note->id,
                'note_type' => $note->note_type,
                'type_label' => ClientNote::getNoteTypes()[$note->note_type] ?? $note->note_type,
                'content' => $note->content,
                'author_name' => $note->author?->name,
                'created_at' => $note->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Engagement / usage / revenue health score, mirroring the host web
     * "Client Score" card. Weighted: engagement 40%, usage 30%, revenue 30%.
     */
    protected function calculateClientScore(Client $client, $host, array $bookingStats): array
    {
        $hostClients = Client::forHost($host->id)->active()->get();
        $avgLifetimeValue = $hostClients->avg('lifetime_value') ?: 100;
        $avgClassesAttended = $hostClients->avg('total_classes_attended') ?: 5;

        // === Engagement (0-100): attendance rate + recency + frequency ===
        $engagementScore = 0;

        $totalBooked = $bookingStats['total'] ?: 1;
        $attendedCount = $bookingStats['attended'] ?: 0;
        $attendanceRate = ($attendedCount / $totalBooked) * 100;
        $engagementScore += min(40, ($attendanceRate / 100) * 40);

        $daysSinceLastVisit = $client->last_visit_at
            ? (int) now()->diffInDays($client->last_visit_at)
            : 365;
        if ($daysSinceLastVisit <= 7) {
            $engagementScore += 30;
        } elseif ($daysSinceLastVisit <= 14) {
            $engagementScore += 25;
        } elseif ($daysSinceLastVisit <= 30) {
            $engagementScore += 20;
        } elseif ($daysSinceLastVisit <= 60) {
            $engagementScore += 10;
        } elseif ($daysSinceLastVisit <= 90) {
            $engagementScore += 5;
        }

        $clientAge = $client->created_at ? max(1, now()->diffInMonths($client->created_at)) : 1;
        $bookingsPerMonth = $bookingStats['total'] / $clientAge;
        if ($bookingsPerMonth >= 8) {
            $engagementScore += 30;
        } elseif ($bookingsPerMonth >= 4) {
            $engagementScore += 25;
        } elseif ($bookingsPerMonth >= 2) {
            $engagementScore += 20;
        } elseif ($bookingsPerMonth >= 1) {
            $engagementScore += 15;
        } elseif ($bookingsPerMonth >= 0.5) {
            $engagementScore += 10;
        }

        // === Usage (0-100): classes + services + membership ===
        $usageScore = min(40, ($client->total_classes_attended / max(1, $avgClassesAttended * 2)) * 40);
        $usageScore += min(30, (($client->total_services_booked ?? 0) / 10) * 30);
        if ($client->membership_status === 'active') {
            $usageScore += 30;
        } elseif ($client->membership_status === 'paused') {
            $usageScore += 15;
        } elseif ($client->status === Client::STATUS_ACTIVE) {
            $usageScore += 10;
        }

        // === Revenue (0-100): lifetime value vs host average ===
        $revenueScore = 0;
        $lifetimeValue = (float) ($client->lifetime_value ?? $client->total_spent ?? 0);
        if ($avgLifetimeValue > 0) {
            $revenueRatio = $lifetimeValue / $avgLifetimeValue;
            $revenueScore = match (true) {
                $revenueRatio >= 2 => 100,
                $revenueRatio >= 1.5 => 85,
                $revenueRatio >= 1 => 70,
                $revenueRatio >= 0.75 => 55,
                $revenueRatio >= 0.5 => 40,
                $revenueRatio >= 0.25 => 25,
                $lifetimeValue > 0 => 15,
                default => 0,
            };
        } elseif ($lifetimeValue > 0) {
            $revenueScore = 50;
        }

        $overallScore = (int) round(($engagementScore * 0.4) + ($usageScore * 0.3) + ($revenueScore * 0.3));

        $grade = match (true) {
            $overallScore >= 90 => ['label' => 'A+', 'color' => 'success', 'description' => 'Excellent'],
            $overallScore >= 80 => ['label' => 'A', 'color' => 'success', 'description' => 'Great'],
            $overallScore >= 70 => ['label' => 'B', 'color' => 'info', 'description' => 'Good'],
            $overallScore >= 60 => ['label' => 'C', 'color' => 'warning', 'description' => 'Average'],
            $overallScore >= 50 => ['label' => 'D', 'color' => 'warning', 'description' => 'Below Average'],
            default => ['label' => 'F', 'color' => 'error', 'description' => 'Needs Attention'],
        };

        return [
            'overall' => $overallScore,
            'grade' => $grade,
            'engagement' => [
                'score' => (int) round($engagementScore),
                'days_since_visit' => $daysSinceLastVisit,
            ],
            'usage' => ['score' => (int) round($usageScore)],
            'revenue' => ['score' => (int) round($revenueScore)],
        ];
    }
}
