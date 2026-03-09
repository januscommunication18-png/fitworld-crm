<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Tag;
use App\Models\ClientFieldDefinition;
use App\Models\ClientFieldSection;
use App\Models\ClientNote;
use App\Rules\ValidName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    protected function getHost()
    {
        $host = Auth::user()->currentHost() ?? Auth::user()->host;

        if (!$host) {
            abort(403, 'No studio access. Please select a studio.');
        }

        return $host;
    }

    /**
     * Display a listing of all clients.
     */
    public function index(Request $request)
    {
        $host = $this->getHost();
        $query = Client::forHost($host->id)->active();

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
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

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $clients = $query->with('tags')->paginate(25)->withQueryString();
        $tags = Tag::forHost($host->id)->orderBy('name')->get();

        return view('host.clients.index', [
            'clients' => $clients,
            'tags' => $tags,
            'filters' => $request->only(['search', 'status', 'source', 'tag']),
            'statuses' => Client::getStatuses(),
            'sources' => Client::getLeadSources(),
        ]);
    }

    /**
     * Display leads listing.
     */
    public function leads(Request $request)
    {
        $host = $this->getHost();
        $query = Client::forHost($host->id)->active()->leads();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('source')) {
            $query->withSource($request->source);
        }

        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $clients = $query->with('tags')->paginate(25)->withQueryString();
        $tags = Tag::forHost($host->id)->orderBy('name')->get();

        return view('host.clients.leads', [
            'clients' => $clients,
            'tags' => $tags,
            'filters' => $request->only(['search', 'source', 'tag']),
            'sources' => Client::getLeadSources(),
        ]);
    }

    /**
     * Display members listing.
     */
    public function members(Request $request)
    {
        $host = $this->getHost();
        $query = Client::forHost($host->id)->active()->members();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('membership_status')) {
            $query->where('membership_status', $request->membership_status);
        }

        if ($request->filled('tag')) {
            $query->withTag($request->tag);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $clients = $query->with('tags')->paginate(25)->withQueryString();
        $tags = Tag::forHost($host->id)->orderBy('name')->get();

        return view('host.clients.members', [
            'clients' => $clients,
            'tags' => $tags,
            'filters' => $request->only(['search', 'membership_status', 'tag']),
            'membershipStatuses' => Client::getMembershipStatuses(),
        ]);
    }

    /**
     * Display at-risk clients listing.
     */
    public function atRisk(Request $request)
    {
        $host = $this->getHost();
        $query = Client::forHost($host->id)->active()->atRisk();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $clients = $query->with('tags')->paginate(25)->withQueryString();

        return view('host.clients.at-risk', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        $host = $this->getHost();
        $tags = Tag::forHost($host->id)->orderBy('name')->get();
        $customFields = $this->getCustomFieldsForForm($host->id, 'add');

        return view('host.clients.create', [
            'tags' => $tags,
            'customFields' => $customFields,
            'statuses' => Client::getStatuses(),
            'sources' => Client::getLeadSources(),
            'genders' => Client::getGenders(),
            'experienceLevels' => Client::getExperienceLevels(),
            'contactMethods' => Client::getContactMethods(),
            'membershipStatuses' => Client::getMembershipStatuses(),
        ]);
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $host = $this->getHost();

        $validated = $request->validate([
            // Basic Information
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['required', 'string', 'max:50', new ValidName],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(array_keys(Client::getGenders()))],

            // Contact Details
            'address' => ['nullable', 'array'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],

            // Status
            'status' => ['required', Rule::in(array_keys(Client::getStatuses()))],
            'membership_status' => ['nullable', Rule::in(array_keys(Client::getMembershipStatuses()))],
            'membership_start_date' => ['nullable', 'date'],
            'membership_end_date' => ['nullable', 'date'],
            'membership_renewal_date' => ['nullable', 'date'],

            // Source & Marketing
            'lead_source' => ['required', Rule::in(array_keys(Client::getLeadSources()))],
            'source_url' => ['nullable', 'string', 'max:500'],
            'referral_source' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],

            // Communication Preferences
            'email_opt_in' => ['nullable', 'boolean'],
            'sms_opt_in' => ['nullable', 'boolean'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'preferred_contact_method' => ['nullable', 'array'],
            'preferred_contact_method.*' => ['nullable', Rule::in(array_keys(Client::getContactMethods()))],

            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_email' => ['nullable', 'email', 'max:255'],

            // Health & Fitness
            'medical_conditions' => ['nullable', 'string'],
            'injuries' => ['nullable', 'string'],
            'limitations' => ['nullable', 'string'],
            'fitness_goals' => ['nullable', 'string'],
            'experience_level' => ['nullable', Rule::in(array_keys(Client::getExperienceLevels()))],
            'pregnancy_status' => ['nullable', 'boolean'],

            // Internal
            'notes' => ['nullable', 'string'],

            // Tags & Custom Fields
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        // Build client data array
        $clientData = [
            'host_id' => $host->id,
            'created_by_user_id' => Auth::id(),

            // Basic Information
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'secondary_phone' => $validated['secondary_phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,

            // Contact Details
            'address' => $validated['address'] ?? null,
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state_province' => $validated['state_province'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,

            // Status
            'status' => $validated['status'],
            'membership_status' => $validated['membership_status'] ?? Client::MEMBERSHIP_NONE,
            'membership_start_date' => $validated['membership_start_date'] ?? null,
            'membership_end_date' => $validated['membership_end_date'] ?? null,
            'membership_renewal_date' => $validated['membership_renewal_date'] ?? null,

            // Source & Marketing
            'lead_source' => $validated['lead_source'],
            'source_url' => $validated['source_url'] ?? null,
            'referral_source' => $validated['referral_source'] ?? null,
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_medium' => $validated['utm_medium'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'utm_term' => $validated['utm_term'] ?? null,
            'utm_content' => $validated['utm_content'] ?? null,

            // Communication Preferences
            'email_opt_in' => $validated['email_opt_in'] ?? true,
            'sms_opt_in' => $validated['sms_opt_in'] ?? false,
            'marketing_opt_in' => $validated['marketing_opt_in'] ?? true,
            'preferred_contact_method' => !empty($validated['preferred_contact_method']) ? implode(',', $validated['preferred_contact_method']) : 'email',

            // Emergency Contact
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'emergency_contact_email' => $validated['emergency_contact_email'] ?? null,

            // Health & Fitness
            'medical_conditions' => $validated['medical_conditions'] ?? null,
            'injuries' => $validated['injuries'] ?? null,
            'limitations' => $validated['limitations'] ?? null,
            'fitness_goals' => $validated['fitness_goals'] ?? null,
            'experience_level' => $validated['experience_level'] ?? null,
            'pregnancy_status' => $validated['pregnancy_status'] ?? null,

            // Internal
            'notes' => $validated['notes'] ?? null,
        ];

        $client = Client::create($clientData);

        // Attach tags
        if (!empty($validated['tags'])) {
            $client->tags()->attach($validated['tags']);
            Tag::whereIn('id', $validated['tags'])->each(fn($tag) => $tag->updateUsageCount());
        }

        // Save custom fields
        if (!empty($validated['custom_fields'])) {
            $this->saveCustomFieldValues($client, $validated['custom_fields']);
        }

        // Add system note
        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => ClientNote::TYPE_SYSTEM,
            'content' => 'Client created',
        ]);

        return redirect()->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client.
     */
    public function show(int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $client->load(['tags', 'clientNotes.author', 'fieldValues.fieldDefinition']);
        $customFields = $this->getCustomFieldsWithValues($client);

        // Load all bookings for this client
        $bookings = \App\Models\Booking::forClient($client->id)
            ->with(['bookable.location', 'client'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Eager load the correct instructor relationship based on bookable type
        $bookings->each(function ($booking) {
            if ($booking->bookable instanceof \App\Models\ClassSession) {
                $booking->bookable->load(['primaryInstructor', 'classPlan']);
            } elseif ($booking->bookable instanceof \App\Models\ServiceSlot) {
                $booking->bookable->load(['instructor', 'servicePlan']);
            }
        });

        // Calculate booking stats
        $bookingStats = [
            'total' => $bookings->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'attended' => $bookings->whereNotNull('checked_in_at')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'no_show' => $bookings->where('status', 'no_show')->count(),
        ];

        // Calculate booking summary by class/service
        $classesTaken = $bookings->filter(fn($b) => $b->bookable instanceof \App\Models\ClassSession)
            ->groupBy(fn($b) => $b->bookable->classPlan->name ?? 'Unknown Class')
            ->map(fn($group) => [
                'count' => $group->count(),
                'attended' => $group->whereNotNull('checked_in_at')->count(),
                'icon' => 'yoga',
            ])
            ->sortByDesc('count');

        $servicesTaken = $bookings->filter(fn($b) => $b->bookable instanceof \App\Models\ServiceSlot)
            ->groupBy(fn($b) => $b->bookable->servicePlan->name ?? 'Unknown Service')
            ->map(fn($group) => [
                'count' => $group->count(),
                'attended' => $group->whereNotNull('checked_in_at')->count(),
                'icon' => 'massage',
            ])
            ->sortByDesc('count');

        // Get memberships for this client
        $membershipsTaken = $client->customerMemberships()
            ->with('membershipPlan')
            ->get()
            ->groupBy(fn($m) => $m->membershipPlan->name ?? 'Unknown Membership')
            ->map(fn($group) => [
                'count' => $group->count(),
                'active' => $group->where('status', 'active')->count(),
            ])
            ->sortByDesc('count');

        // Get class pass/package purchases
        $catalogPurchases = $client->classPassPurchases()
            ->with('classPass')
            ->get()
            ->groupBy(fn($p) => $p->classPass->name ?? 'Unknown Package')
            ->map(fn($group) => [
                'count' => $group->count(),
                'total_spent' => $group->sum('price_paid'),
            ])
            ->sortByDesc('count');

        $bookingSummary = [
            'classes' => $classesTaken,
            'services' => $servicesTaken,
            'memberships' => $membershipsTaken,
            'catalog' => $catalogPurchases,
            'top_class' => $classesTaken->keys()->first(),
            'top_class_count' => $classesTaken->first()['count'] ?? 0,
            'top_service' => $servicesTaken->keys()->first(),
            'top_service_count' => $servicesTaken->first()['count'] ?? 0,
            'total_classes' => $classesTaken->sum('count'),
            'total_services' => $servicesTaken->sum('count'),
            'total_memberships' => $membershipsTaken->sum('count'),
            'total_catalog' => $catalogPurchases->sum('count'),
        ];

        // Load questionnaire responses with their bookings
        $questionnaireResponses = $client->questionnaireResponses()
            ->with(['version.questionnaire', 'booking.bookable'])
            ->latest()
            ->get();

        // Load booking info for each questionnaire response
        $questionnaireResponses->each(function ($response) {
            if ($response->booking && $response->booking->bookable) {
                if ($response->booking->bookable instanceof \App\Models\ClassSession) {
                    $response->booking->bookable->load('classPlan');
                } elseif ($response->booking->bookable instanceof \App\Models\ServiceSlot) {
                    $response->booking->bookable->load('servicePlan');
                }
            }
        });

        // Load active customer membership with plan
        $activeCustomerMembership = $client->customerMemberships()
            ->where('status', \App\Models\CustomerMembership::STATUS_ACTIVE)
            ->with('membershipPlan')
            ->latest()
            ->first();

        // Load progress reports with pagination for display
        $progressReports = $client->progressReports()
            ->with([
                'template.sections.metrics',
                'values.metric.section',
                'classSession.classPlan',
                'recordedBy',
                'photos',
            ])
            ->orderBy('report_date', 'desc')
            ->paginate(10, ['*'], 'progress_page')
            ->appends(['tab' => 'progress']);

        // Get progress data grouped by template
        $progressByTemplate = $client->progressReports()
            ->whereNotNull('overall_score')
            ->with(['template', 'classSession.classPlan'])
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy('progress_template_id')
            ->map(function ($reports) {
                $template = $reports->first()->template;
                $latestScore = $reports->first()->overall_score;
                $previousScore = $reports->count() > 1 ? $reports->skip(1)->first()->overall_score : null;
                $trend = $previousScore !== null ? $latestScore - $previousScore : 0;

                // Group by class
                $byClass = $reports->groupBy(fn($r) => $r->classSession?->classPlan?->name ?? 'General')
                    ->map(function ($classReports) {
                        return [
                            'count' => $classReports->count(),
                            'latest_score' => $classReports->first()->overall_score,
                            'average_score' => round($classReports->avg('overall_score'), 1),
                        ];
                    });

                return [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                    'template_icon' => $template->icon ?? 'chart-line',
                    'total_reports' => $reports->count(),
                    'latest_score' => round($latestScore, 1),
                    'average_score' => round($reports->avg('overall_score'), 1),
                    'trend' => round($trend, 1),
                    'latest_date' => $reports->first()->report_date->format('M j, Y'),
                    'by_class' => $byClass,
                    'chart_data' => $reports->take(10)->reverse()->values()->map(fn($r) => [
                        'date' => $r->report_date->format('M j'),
                        'score' => round($r->overall_score, 1),
                        'class' => $r->classSession?->classPlan?->name ?? 'General',
                    ]),
                ];
            });

        // Get this week's schedule for the client
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $thisWeekSchedule = \App\Models\Booking::forClient($client->id)
            ->whereHas('bookable', function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('start_time', [$weekStart, $weekEnd]);
            })
            ->with(['bookable.location'])
            ->get()
            ->map(function ($booking) {
                $booking->bookable->load(
                    $booking->bookable instanceof \App\Models\ClassSession
                        ? ['classPlan', 'primaryInstructor']
                        : ['servicePlan', 'instructor']
                );
                return $booking;
            })
            ->sortBy(fn($b) => $b->bookable->start_time);

        // Get today's class sessions this client is booked for (for Record Progress modal)
        // Include the class plan's associated progress templates
        $todaysClasses = \App\Models\ClassSession::whereHas('bookings', function ($q) use ($client) {
                $q->where('client_id', $client->id)
                  ->where('status', 'confirmed');
            })
            ->whereDate('start_time', now()->toDateString())
            ->with(['classPlan.progressTemplates', 'primaryInstructor', 'location'])
            ->orderBy('start_time')
            ->get();

        // Check if any of today's classes have progress templates
        $hasProgressTemplates = $todaysClasses->contains(function ($class) {
            return $class->classPlan && $class->classPlan->progressTemplates->count() > 0;
        });

        // Calculate Client Score (Engagement, Usage, Revenue)
        $clientScore = $this->calculateClientScore($client, $bookings, $bookingStats);

        return view('host.clients.show', [
            'client' => $client,
            'customFields' => $customFields,
            'statuses' => Client::getStatuses(),
            'bookings' => $bookings,
            'bookingStats' => $bookingStats,
            'bookingSummary' => $bookingSummary,
            'questionnaireResponses' => $questionnaireResponses,
            'activeCustomerMembership' => $activeCustomerMembership,
            'progressReports' => $progressReports,
            'progressByTemplate' => $progressByTemplate,
            'thisWeekSchedule' => $thisWeekSchedule,
            'todaysClasses' => $todaysClasses,
            'hasProgressTemplates' => $hasProgressTemplates,
            'clientScore' => $clientScore,
        ]);
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $host = $this->getHost();
        $tags = Tag::forHost($host->id)->orderBy('name')->get();
        $customFields = $this->getCustomFieldsForForm($host->id, 'edit', $client);

        return view('host.clients.edit', [
            'client' => $client,
            'tags' => $tags,
            'customFields' => $customFields,
            'statuses' => Client::getStatuses(),
            'sources' => Client::getLeadSources(),
            'genders' => Client::getGenders(),
            'experienceLevels' => Client::getExperienceLevels(),
            'contactMethods' => Client::getContactMethods(),
            'membershipStatuses' => Client::getMembershipStatuses(),
        ]);
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $validated = $request->validate([
            // Basic Information
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['required', 'string', 'max:50', new ValidName],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(array_keys(Client::getGenders()))],

            // Contact Details
            'address' => ['nullable', 'array'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],

            // Status
            'status' => ['required', Rule::in(array_keys(Client::getStatuses()))],
            'membership_status' => ['nullable', Rule::in(array_keys(Client::getMembershipStatuses()))],
            'membership_start_date' => ['nullable', 'date'],
            'membership_end_date' => ['nullable', 'date'],
            'membership_renewal_date' => ['nullable', 'date'],

            // Source & Marketing
            'lead_source' => ['required', Rule::in(array_keys(Client::getLeadSources()))],
            'source_url' => ['nullable', 'string', 'max:500'],
            'referral_source' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],

            // Communication Preferences
            'email_opt_in' => ['nullable', 'boolean'],
            'sms_opt_in' => ['nullable', 'boolean'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'preferred_contact_method' => ['nullable', 'array'],
            'preferred_contact_method.*' => ['nullable', Rule::in(array_keys(Client::getContactMethods()))],

            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_email' => ['nullable', 'email', 'max:255'],

            // Health & Fitness
            'medical_conditions' => ['nullable', 'string'],
            'injuries' => ['nullable', 'string'],
            'limitations' => ['nullable', 'string'],
            'fitness_goals' => ['nullable', 'string'],
            'experience_level' => ['nullable', Rule::in(array_keys(Client::getExperienceLevels()))],
            'pregnancy_status' => ['nullable', 'boolean'],

            // Internal
            'notes' => ['nullable', 'string'],

            // Tags & Custom Fields
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        // Build update data array
        $updateData = [
            'updated_by_user_id' => Auth::id(),

            // Basic Information
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'secondary_phone' => $validated['secondary_phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,

            // Contact Details
            'address' => $validated['address'] ?? null,
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state_province' => $validated['state_province'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,

            // Status
            'status' => $validated['status'],
            'membership_status' => $validated['membership_status'] ?? $client->membership_status,
            'membership_start_date' => $validated['membership_start_date'] ?? null,
            'membership_end_date' => $validated['membership_end_date'] ?? null,
            'membership_renewal_date' => $validated['membership_renewal_date'] ?? null,

            // Source & Marketing
            'lead_source' => $validated['lead_source'],
            'source_url' => $validated['source_url'] ?? null,
            'referral_source' => $validated['referral_source'] ?? null,
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_medium' => $validated['utm_medium'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'utm_term' => $validated['utm_term'] ?? null,
            'utm_content' => $validated['utm_content'] ?? null,

            // Communication Preferences
            'email_opt_in' => $request->has('email_opt_in') ? $validated['email_opt_in'] : $client->email_opt_in,
            'sms_opt_in' => $request->has('sms_opt_in') ? $validated['sms_opt_in'] : $client->sms_opt_in,
            'marketing_opt_in' => $request->has('marketing_opt_in') ? $validated['marketing_opt_in'] : $client->marketing_opt_in,
            'preferred_contact_method' => !empty($validated['preferred_contact_method']) ? implode(',', $validated['preferred_contact_method']) : $client->preferred_contact_method,

            // Emergency Contact
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'emergency_contact_email' => $validated['emergency_contact_email'] ?? null,

            // Health & Fitness
            'medical_conditions' => $validated['medical_conditions'] ?? null,
            'injuries' => $validated['injuries'] ?? null,
            'limitations' => $validated['limitations'] ?? null,
            'fitness_goals' => $validated['fitness_goals'] ?? null,
            'experience_level' => $validated['experience_level'] ?? null,
            'pregnancy_status' => $request->has('pregnancy_status') ? $validated['pregnancy_status'] : $client->pregnancy_status,

            // Internal
            'notes' => $validated['notes'] ?? null,
        ];

        $client->update($updateData);

        // Sync tags
        $oldTags = $client->tags()->pluck('tags.id')->toArray();
        $newTags = $validated['tags'] ?? [];
        $client->tags()->sync($newTags);

        // Update usage counts for affected tags
        $affectedTags = array_unique(array_merge($oldTags, $newTags));
        Tag::whereIn('id', $affectedTags)->each(fn($tag) => $tag->updateUsageCount());

        // Save custom fields
        if (!empty($validated['custom_fields'])) {
            $this->saveCustomFieldValues($client, $validated['custom_fields']);
        }

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Archive the specified client.
     */
    public function archive(int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $client->archive();

        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => ClientNote::TYPE_SYSTEM,
            'content' => 'Client archived',
        ]);

        return redirect()->route('clients.index')
            ->with('success', 'Client archived successfully.');
    }

    /**
     * Restore an archived client.
     */
    public function restore(int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $client->restore();

        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => ClientNote::TYPE_SYSTEM,
            'content' => 'Client restored from archive',
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client restored successfully.');
    }

    /**
     * Add a note to a client.
     */
    public function addNote(Request $request, int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $validated = $request->validate([
            'note_type' => ['required', Rule::in(array_keys(ClientNote::getNoteTypes()))],
            'content' => ['required', 'string'],
        ]);

        $note = ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note added successfully.',
                'note' => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'note_type' => $note->note_type,
                    'icon' => ClientNote::getNoteTypeIcon($note->note_type),
                    'created_at' => $note->created_at->diffForHumans(),
                ],
            ]);
        }

        return redirect()->route('clients.show', ['id' => $client->id, 'tab' => 'notes'])
            ->with('success', 'Note added successfully.');
    }

    /**
     * Convert lead to client.
     */
    public function convertToClient(int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $client->convertToClient();

        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => ClientNote::TYPE_SYSTEM,
            'content' => 'Converted from lead to client',
        ]);

        return back()->with('success', 'Lead converted to client successfully.');
    }

    /**
     * Convert to member.
     */
    public function convertToMember(int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $client->convertToMember();

        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => ClientNote::TYPE_SYSTEM,
            'content' => 'Converted to member',
        ]);

        return back()->with('success', 'Client converted to member successfully.');
    }

    /**
     * Clear at-risk status.
     */
    public function clearAtRisk(int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $client->clearAtRisk();

        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => ClientNote::TYPE_SYSTEM,
            'content' => 'At-risk status cleared',
        ]);

        return back()->with('success', 'At-risk status cleared.');
    }

    /**
     * Add/remove tags from client (AJAX).
     */
    public function updateTags(Request $request, int $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeClient($client);

        $validated = $request->validate([
            'tags' => ['required', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        $oldTags = $client->tags()->pluck('tags.id')->toArray();
        $client->tags()->sync($validated['tags']);

        // Update usage counts
        $affectedTags = array_unique(array_merge($oldTags, $validated['tags']));
        Tag::whereIn('id', $affectedTags)->each(fn($tag) => $tag->updateUsageCount());

        return response()->json(['success' => true]);
    }

    /**
     * Authorize that the client belongs to the current host.
     */
    protected function authorizeClient(Client $client): void
    {
        $host = $this->getHost();

        if ($client->host_id !== $host->id) {
            abort(403, 'This client belongs to a different studio. Please switch to the correct studio to view this client.');
        }
    }

    /**
     * Calculate client score based on engagement, usage, and revenue.
     */
    protected function calculateClientScore(Client $client, $bookings, array $bookingStats): array
    {
        // Get host's average values for comparison (for relative scoring)
        $host = $this->getHost();
        $hostClients = Client::forHost($host->id)->active()->get();
        $avgLifetimeValue = $hostClients->avg('lifetime_value') ?: 100;
        $avgClassesAttended = $hostClients->avg('total_classes_attended') ?: 5;

        // === ENGAGEMENT SCORE (0-100) ===
        // Based on: attendance rate, recency, frequency
        $engagementScore = 0;

        // Attendance rate (40 points max)
        $totalBooked = $bookingStats['total'] ?: 1;
        $attendedCount = $bookingStats['attended'] ?: 0;
        $attendanceRate = ($attendedCount / $totalBooked) * 100;
        $engagementScore += min(40, ($attendanceRate / 100) * 40);

        // Recency - days since last visit (30 points max)
        $daysSinceLastVisit = $client->last_visit_at
            ? now()->diffInDays($client->last_visit_at)
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

        // Frequency - bookings per month (30 points max)
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

        // === USAGE SCORE (0-100) ===
        // Based on: total classes, services, membership status
        $usageScore = 0;

        // Total classes attended (40 points max)
        $classScore = min(40, ($client->total_classes_attended / max(1, $avgClassesAttended * 2)) * 40);
        $usageScore += $classScore;

        // Total services booked (30 points max)
        $serviceScore = min(30, ($client->total_services_booked / 10) * 30);
        $usageScore += $serviceScore;

        // Membership status (30 points max)
        if ($client->membership_status === 'active') {
            $usageScore += 30;
        } elseif ($client->membership_status === 'paused') {
            $usageScore += 15;
        } elseif ($client->status === 'member') {
            $usageScore += 20;
        } elseif ($client->status === 'client') {
            $usageScore += 10;
        }

        // === REVENUE SCORE (0-100) ===
        // Based on: lifetime value compared to average
        $revenueScore = 0;
        $lifetimeValue = (float) ($client->lifetime_value ?? $client->total_spent ?? 0);

        if ($avgLifetimeValue > 0) {
            $revenueRatio = $lifetimeValue / $avgLifetimeValue;
            if ($revenueRatio >= 2) {
                $revenueScore = 100;
            } elseif ($revenueRatio >= 1.5) {
                $revenueScore = 85;
            } elseif ($revenueRatio >= 1) {
                $revenueScore = 70;
            } elseif ($revenueRatio >= 0.75) {
                $revenueScore = 55;
            } elseif ($revenueRatio >= 0.5) {
                $revenueScore = 40;
            } elseif ($revenueRatio >= 0.25) {
                $revenueScore = 25;
            } elseif ($lifetimeValue > 0) {
                $revenueScore = 15;
            }
        } elseif ($lifetimeValue > 0) {
            $revenueScore = 50; // Has revenue but no comparison
        }

        // === OVERALL SCORE ===
        // Weighted average: Engagement 40%, Usage 30%, Revenue 30%
        $overallScore = round(
            ($engagementScore * 0.4) +
            ($usageScore * 0.3) +
            ($revenueScore * 0.3)
        );

        // Determine grade
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
                'score' => round($engagementScore),
                'attendance_rate' => round($attendanceRate),
                'days_since_visit' => $daysSinceLastVisit,
                'bookings_per_month' => round($bookingsPerMonth, 1),
            ],
            'usage' => [
                'score' => round($usageScore),
                'total_classes' => $client->total_classes_attended ?? 0,
                'total_services' => $client->total_services_booked ?? 0,
                'membership_status' => $client->membership_status,
            ],
            'revenue' => [
                'score' => round($revenueScore),
                'lifetime_value' => $lifetimeValue,
                'avg_lifetime_value' => round($avgLifetimeValue, 2),
            ],
        ];
    }

    /**
     * Get custom fields for the add/edit form.
     */
    protected function getCustomFieldsForForm(int $hostId, string $formType, ?Client $client = null): array
    {
        $showColumn = $formType === 'add' ? 'show_on_add' : 'show_on_edit';

        $sections = ClientFieldSection::forHost($hostId)
            ->active()
            ->ordered()
            ->with(['activeFieldDefinitions' => function ($query) use ($showColumn) {
                $query->where($showColumn, true)->ordered();
            }])
            ->get();

        $unsectionedFields = ClientFieldDefinition::forHost($hostId)
            ->active()
            ->withoutSection()
            ->where($showColumn, true)
            ->ordered()
            ->get();

        // Load values if editing
        $values = [];
        if ($client) {
            $values = $client->fieldValues()
                ->with('fieldDefinition')
                ->get()
                ->keyBy('fieldDefinition.field_key')
                ->map(fn($v) => $v->value)
                ->toArray();
        }

        return [
            'sections' => $sections,
            'unsectionedFields' => $unsectionedFields,
            'values' => $values,
        ];
    }

    /**
     * Get custom fields with values for display.
     */
    protected function getCustomFieldsWithValues(Client $client): array
    {
        $sections = ClientFieldSection::forHost($client->host_id)
            ->active()
            ->ordered()
            ->with(['activeFieldDefinitions' => function ($query) {
                $query->ordered();
            }])
            ->get();

        $unsectionedFields = ClientFieldDefinition::forHost($client->host_id)
            ->active()
            ->withoutSection()
            ->ordered()
            ->get();

        $values = $client->fieldValues()
            ->with('fieldDefinition')
            ->get()
            ->keyBy('field_definition_id');

        return [
            'sections' => $sections,
            'unsectionedFields' => $unsectionedFields,
            'values' => $values,
        ];
    }

    /**
     * Save custom field values for a client.
     */
    protected function saveCustomFieldValues(Client $client, array $customFields): void
    {
        foreach ($customFields as $fieldKey => $value) {
            $definition = ClientFieldDefinition::forHost($client->host_id)
                ->where('field_key', $fieldKey)
                ->first();

            if ($definition) {
                // Handle checkbox values (array)
                if ($definition->field_type === ClientFieldDefinition::TYPE_CHECKBOX && is_array($value)) {
                    $value = json_encode($value);
                }

                $client->fieldValues()->updateOrCreate(
                    ['field_definition_id' => $definition->id],
                    ['value' => $value]
                );
            }
        }
    }
}
