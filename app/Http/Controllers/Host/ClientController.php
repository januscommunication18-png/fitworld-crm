<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Tag;
use App\Models\ClientFieldDefinition;
use App\Models\ClientFieldSection;
use App\Models\ClientNote;
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
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

        // Load recent bookings for this client
        $bookings = \App\Models\Booking::forClient($client->id)
            ->with(['bookable.primaryInstructor', 'bookable.location', 'client'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('host.clients.show', [
            'client' => $client,
            'customFields' => $customFields,
            'statuses' => Client::getStatuses(),
            'bookings' => $bookings,
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
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

        ClientNote::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
        ]);

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
