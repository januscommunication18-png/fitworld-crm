<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\QuestionnaireVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionnaireController extends Controller
{
    /**
     * Display a listing of questionnaires.
     */
    public function index(Request $request)
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;
        $status = $request->get('status');
        $type = $request->get('type');

        $questionnaires = $host->questionnaires()
            ->with(['activeVersion.steps', 'activeVersion.blocks', 'latestVersion.steps', 'latestVersion.blocks', 'creator'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('type', $type))
            ->latest()
            ->paginate(20);

        $statuses = Questionnaire::getStatuses();
        $types = Questionnaire::getTypes();

        // Get counts for filter badges
        $counts = [
            'all' => $host->questionnaires()->count(),
            'draft' => $host->questionnaires()->draft()->count(),
            'active' => $host->questionnaires()->active()->count(),
            'archived' => $host->questionnaires()->archived()->count(),
        ];

        return view('host.questionnaires.index', compact(
            'questionnaires',
            'status',
            'type',
            'statuses',
            'types',
            'counts'
        ));
    }

    /**
     * Show the form for creating a new questionnaire.
     */
    public function create()
    {
        $types = Questionnaire::getTypes();

        return view('host.questionnaires.create', compact('types'));
    }

    /**
     * Store a newly created questionnaire.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:single,wizard',
            'estimated_minutes' => 'nullable|integer|min:1|max:60',
        ]);

        $host = auth()->user()->currentHost() ?? auth()->user()->host;
        $user = auth()->user();

        $questionnaire = $host->questionnaires()->create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'estimated_minutes' => $request->estimated_minutes,
            'status' => Questionnaire::STATUS_DRAFT,
            'created_by' => $user->id,
        ]);

        // Create initial version
        $questionnaire->versions()->create([
            'version_number' => 1,
            'status' => QuestionnaireVersion::STATUS_DRAFT,
            'created_by' => $user->id,
        ]);

        return redirect()->route('questionnaires.builder', $questionnaire)
            ->with('success', 'Questionnaire created. Start building your form!');
    }

    /**
     * Display the specified questionnaire (responses view).
     */
    public function show(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $questionnaire->load(['activeVersion', 'latestVersion', 'attachments']);

        // Get response stats
        $responseStats = [
            'total' => $questionnaire->activeVersion?->responses()->count() ?? 0,
            'completed' => $questionnaire->activeVersion?->responses()->completed()->count() ?? 0,
            'pending' => $questionnaire->activeVersion?->responses()->incomplete()->count() ?? 0,
        ];

        return view('host.questionnaires.show', compact('questionnaire', 'responseStats'));
    }

    /**
     * Show the form for editing questionnaire settings.
     */
    public function edit(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $types = Questionnaire::getTypes();

        return view('host.questionnaires.edit', compact('questionnaire', 'types'));
    }

    /**
     * Update the specified questionnaire settings.
     */
    public function update(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'estimated_minutes' => 'nullable|integer|min:1|max:60',
            'intro_text' => 'nullable|string|max:2000',
            'thank_you_message' => 'nullable|string|max:2000',
            'allow_save_resume' => 'boolean',
        ]);

        $questionnaire->update([
            'name' => $request->name,
            'description' => $request->description,
            'estimated_minutes' => $request->estimated_minutes,
            'intro_text' => $request->intro_text,
            'thank_you_message' => $request->thank_you_message,
            'allow_save_resume' => $request->boolean('allow_save_resume', true),
        ]);

        return redirect()->route('questionnaires.show', $questionnaire)
            ->with('success', 'Questionnaire settings updated.');
    }

    /**
     * Remove the specified questionnaire.
     */
    public function destroy(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        // Check if there are any responses
        $hasResponses = $questionnaire->versions()
            ->whereHas('responses')
            ->exists();

        if ($hasResponses) {
            // Archive instead of delete if there are responses
            $questionnaire->archive();

            return redirect()->route('questionnaires.index')
                ->with('info', 'Questionnaire has responses and was archived instead of deleted.');
        }

        $questionnaire->delete();

        return redirect()->route('questionnaires.index')
            ->with('success', 'Questionnaire deleted.');
    }

    /**
     * Display the questionnaire builder.
     */
    public function builder(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        // Get or create draft version for editing
        $version = $questionnaire->getOrCreateDraftVersion(auth()->id());

        // Load full structure
        $version->load([
            'steps' => fn($q) => $q->orderBy('sort_order'),
            'steps.blocks' => fn($q) => $q->orderBy('sort_order'),
            'steps.blocks.questions' => fn($q) => $q->orderBy('sort_order'),
            'blocks' => fn($q) => $q->orderBy('sort_order'),
            'blocks.questions' => fn($q) => $q->orderBy('sort_order'),
        ]);

        $questionTypes = \App\Models\QuestionnaireQuestion::getQuestionTypes();

        return view('host.questionnaires.builder', compact('questionnaire', 'version', 'questionTypes'));
    }

    /**
     * Preview the questionnaire as a client would see it.
     */
    public function preview(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $version = $questionnaire->activeVersion ?? $questionnaire->latestVersion;

        if (!$version) {
            return redirect()->route('questionnaires.builder', $questionnaire)
                ->with('error', 'No version available to preview.');
        }

        $version->load([
            'steps' => fn($q) => $q->orderBy('sort_order'),
            'steps.blocks' => fn($q) => $q->orderBy('sort_order')->where('visibility', 'public'),
            'steps.blocks.questions' => fn($q) => $q->orderBy('sort_order')->where('visibility', 'client'),
            'blocks' => fn($q) => $q->orderBy('sort_order')->where('visibility', 'public'),
            'blocks.questions' => fn($q) => $q->orderBy('sort_order')->where('visibility', 'client'),
        ]);

        return view('host.questionnaires.preview', compact('questionnaire', 'version'));
    }

    /**
     * Publish the draft version.
     */
    public function publish(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $draftVersion = $questionnaire->draftVersion;

        if (!$draftVersion) {
            return redirect()->route('questionnaires.builder', $questionnaire)
                ->with('error', 'No draft version to publish.');
        }

        // Check if there are any questions
        $questionCount = $draftVersion->blocks->sum(fn($b) => $b->questions->count());

        if ($questionCount === 0) {
            return redirect()->route('questionnaires.builder', $questionnaire)
                ->with('error', 'Cannot publish a questionnaire with no questions.');
        }

        $draftVersion->publish();

        return redirect()->route('questionnaires.show', $questionnaire)
            ->with('success', 'Questionnaire published successfully!');
    }

    /**
     * Unpublish (archive) the questionnaire.
     */
    public function unpublish(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $questionnaire->archive();

        return redirect()->route('questionnaires.index')
            ->with('success', 'Questionnaire unpublished.');
    }

    /**
     * Duplicate the questionnaire.
     */
    public function duplicate(Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $newQuestionnaire = $questionnaire->duplicate(auth()->id());

        return redirect()->route('questionnaires.builder', $newQuestionnaire)
            ->with('success', 'Questionnaire duplicated. Edit your copy below.');
    }

    /**
     * Display all responses for a questionnaire.
     */
    public function responses(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $status = $request->get('status');

        $responses = QuestionnaireResponse::with(['client', 'version'])
            ->whereHas('version', fn($q) => $q->where('questionnaire_id', $questionnaire->id))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        $statuses = QuestionnaireResponse::getStatuses();

        // Get counts
        $counts = [
            'all' => QuestionnaireResponse::whereHas('version', fn($q) => $q->where('questionnaire_id', $questionnaire->id))->count(),
            'completed' => QuestionnaireResponse::whereHas('version', fn($q) => $q->where('questionnaire_id', $questionnaire->id))->completed()->count(),
            'pending' => QuestionnaireResponse::whereHas('version', fn($q) => $q->where('questionnaire_id', $questionnaire->id))->incomplete()->count(),
        ];

        return view('host.questionnaires.responses', compact(
            'questionnaire',
            'responses',
            'status',
            'statuses',
            'counts'
        ));
    }

    /**
     * Display a single response.
     */
    public function showResponse(Questionnaire $questionnaire, QuestionnaireResponse $response)
    {
        $this->authorizeHost($questionnaire);

        // Verify response belongs to this questionnaire
        if ($response->version->questionnaire_id !== $questionnaire->id) {
            abort(404);
        }

        $response->load([
            'client',
            'version.steps.blocks.questions',
            'version.blocks.questions',
            'answers.question',
        ]);

        return view('host.questionnaires.response-detail', compact('questionnaire', 'response'));
    }

    /**
     * Create a new response and return the link (for manual sending).
     */
    public function createResponse(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $host = auth()->user()->currentHost() ?? auth()->user()->host;
        $client = Client::where('id', $request->client_id)
            ->where('host_id', $host->id)
            ->firstOrFail();

        // Get active version
        $activeVersion = $questionnaire->activeVersion;

        if (!$activeVersion) {
            return back()->with('error', 'This questionnaire has no published version.');
        }

        // Check if there's already a pending response for this client
        $existingPending = QuestionnaireResponse::where('questionnaire_version_id', $activeVersion->id)
            ->where('client_id', $client->id)
            ->incomplete()
            ->first();

        if ($existingPending) {
            return back()->with('info', 'A pending response already exists for this client.')
                ->with('response_url', $existingPending->getResponseUrl());
        }

        // Create new response
        $response = QuestionnaireResponse::create([
            'questionnaire_version_id' => $activeVersion->id,
            'host_id' => $host->id,
            'client_id' => $client->id,
            'status' => QuestionnaireResponse::STATUS_PENDING,
        ]);

        return back()->with('success', 'Response created. Share the link with the client.')
            ->with('response_url', $response->getResponseUrl());
    }

    /**
     * Resend/regenerate response link for a client.
     */
    public function resendResponse(Questionnaire $questionnaire, QuestionnaireResponse $response)
    {
        $this->authorizeHost($questionnaire);

        // Verify response belongs to this questionnaire
        if ($response->version->questionnaire_id !== $questionnaire->id) {
            abort(404);
        }

        if ($response->isCompleted()) {
            return back()->with('error', 'Cannot resend a completed response.');
        }

        // Regenerate token
        $response->update(['token' => Str::random(64)]);

        return back()->with('success', 'New link generated.')
            ->with('response_url', $response->getResponseUrl());
    }

    /**
     * Verify the questionnaire belongs to the current host.
     */
    protected function authorizeHost(Questionnaire $questionnaire): void
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        if ($questionnaire->host_id !== $host->id) {
            abort(403, 'Unauthorized access to this questionnaire.');
        }
    }
}
