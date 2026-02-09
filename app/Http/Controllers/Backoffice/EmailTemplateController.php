<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\Host;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'system');
        $hostId = $request->get('host_id');

        if ($tab === 'system') {
            $templates = EmailTemplate::whereNull('host_id')
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->groupBy('category');
        } else {
            $templates = EmailTemplate::where('host_id', $hostId)
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->groupBy('category');
        }

        $hosts = Host::orderBy('studio_name')->get(['id', 'studio_name']);

        return view('backoffice.email-templates.index', compact('templates', 'tab', 'hostId', 'hosts'));
    }

    public function create(Request $request)
    {
        $hostId = $request->get('host_id');
        $hosts = Host::orderBy('studio_name')->get(['id', 'studio_name']);

        return view('backoffice.email-templates.create', compact('hostId', 'hosts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'host_id' => 'nullable|exists:hosts,id',
            'category' => 'required|in:system,transactional,marketing',
            'key' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Parse variables JSON if provided
        if (!empty($validated['variables'])) {
            $validated['variables'] = json_decode($validated['variables'], true);
        }

        EmailTemplate::create($validated);

        $tab = $validated['host_id'] ? 'client' : 'system';

        return redirect()->route('backoffice.email-templates.index', ['tab' => $tab, 'host_id' => $validated['host_id']])
            ->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $hosts = Host::orderBy('studio_name')->get(['id', 'studio_name']);

        return view('backoffice.email-templates.edit', compact('emailTemplate', 'hosts'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'host_id' => 'nullable|exists:hosts,id',
            'category' => 'required|in:system,transactional,marketing',
            'key' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Parse variables JSON if provided
        if (!empty($validated['variables'])) {
            $validated['variables'] = json_decode($validated['variables'], true);
        }

        $emailTemplate->update($validated);

        $tab = $validated['host_id'] ? 'client' : 'system';

        return redirect()->route('backoffice.email-templates.index', ['tab' => $tab, 'host_id' => $validated['host_id']])
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $tab = $emailTemplate->host_id ? 'client' : 'system';
        $hostId = $emailTemplate->host_id;

        $emailTemplate->delete();

        return redirect()->route('backoffice.email-templates.index', ['tab' => $tab, 'host_id' => $hostId])
            ->with('success', 'Email template deleted successfully.');
    }

    public function preview(EmailTemplate $emailTemplate)
    {
        // Sample data for preview
        $sampleData = [
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'studio_name' => 'Fitness Studio',
            'class_name' => 'Morning Yoga',
            'class_date' => now()->format('F j, Y'),
            'class_time' => '9:00 AM',
            'instructor_name' => 'Jane Smith',
            'booking_reference' => 'BK-123456',
            'app_name' => config('app.name'),
        ];

        $rendered = $emailTemplate->render($sampleData);

        return view('backoffice.email-templates.preview', [
            'emailTemplate' => $emailTemplate,
            'renderedSubject' => $rendered['subject'],
            'renderedHtml' => $rendered['body_html'],
            'renderedText' => $rendered['body_text'],
        ]);
    }

    public function duplicate(EmailTemplate $emailTemplate)
    {
        $newTemplate = $emailTemplate->replicate();
        $newTemplate->name = $emailTemplate->name . ' (Copy)';
        $newTemplate->key = $emailTemplate->key . '_copy_' . time();
        $newTemplate->is_default = false;
        $newTemplate->save();

        return redirect()->route('backoffice.email-templates.edit', $newTemplate)
            ->with('success', 'Email template duplicated successfully.');
    }
}
