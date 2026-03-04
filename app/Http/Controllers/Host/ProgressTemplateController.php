<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\ProgressTemplate;
use App\Models\HostProgressTemplate;
use Illuminate\Http\Request;

class ProgressTemplateController extends Controller
{
    /**
     * Display all available progress templates for browsing.
     */
    public function index()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Check if feature is enabled
        if (!$host->hasFeature('progress-templates')) {
            abort(403, 'User Progress Template feature is not enabled for this studio.');
        }

        // Get enabled template IDs for this host
        $enabledTemplateIds = HostProgressTemplate::where('host_id', $host->id)
            ->where('is_enabled', true)
            ->pluck('progress_template_id')
            ->toArray();

        // Get all active templates
        $templates = ProgressTemplate::active()
            ->ordered()
            ->withCount(['sections', 'sections as metrics_count' => function ($query) {
                $query->join('progress_template_metrics', 'progress_template_sections.id', '=', 'progress_template_metrics.progress_template_section_id')
                    ->selectRaw('count(progress_template_metrics.id)');
            }])
            ->get()
            ->map(function ($template) use ($enabledTemplateIds, $host) {
                $template->is_enabled = in_array($template->id, $enabledTemplateIds);
                $template->is_recommended = $template->isRecommendedForStudio($host);
                // Get actual metrics count
                $template->metrics_count = $template->sections->sum(fn($section) => $section->metrics()->count());
                return $template;
            });

        // Separate into recommended and other templates
        $recommendedTemplates = $templates->filter(fn($t) => $t->is_recommended);
        $otherTemplates = $templates->filter(fn($t) => !$t->is_recommended);

        return view('host.progress-templates.index', compact(
            'templates',
            'recommendedTemplates',
            'otherTemplates',
            'enabledTemplateIds',
            'host'
        ));
    }

    /**
     * Display a specific template with its sections and metrics.
     */
    public function show(ProgressTemplate $progressTemplate)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        if (!$host->hasFeature('progress-templates')) {
            abort(403, 'User Progress Template feature is not enabled for this studio.');
        }

        if (!$progressTemplate->is_active) {
            abort(404);
        }

        $progressTemplate->load(['sections.metrics' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        $hostTemplate = HostProgressTemplate::where('host_id', $host->id)
            ->where('progress_template_id', $progressTemplate->id)
            ->first();

        $isEnabled = $hostTemplate?->is_enabled ?? false;
        $isRecommended = $progressTemplate->isRecommendedForStudio($host);

        return view('host.progress-templates.show', compact(
            'progressTemplate',
            'hostTemplate',
            'isEnabled',
            'isRecommended',
            'host'
        ));
    }

    /**
     * Enable a progress template for the host.
     */
    public function enable(Request $request, ProgressTemplate $progressTemplate)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        if (!$host->hasFeature('progress-templates')) {
            return response()->json([
                'success' => false,
                'message' => 'User Progress Template feature is not enabled for this studio.',
            ], 403);
        }

        if (!$progressTemplate->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This template is not available.',
            ], 404);
        }

        try {
            $hostTemplate = HostProgressTemplate::updateOrCreate(
                [
                    'host_id' => $host->id,
                    'progress_template_id' => $progressTemplate->id,
                ],
                [
                    'is_enabled' => true,
                    'activated_at' => now(),
                    'deactivated_at' => null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "{$progressTemplate->name} has been enabled.",
                'is_enabled' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enable template. Please try again.',
            ], 500);
        }
    }

    /**
     * Disable a progress template for the host.
     */
    public function disable(Request $request, ProgressTemplate $progressTemplate)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        if (!$host->hasFeature('progress-templates')) {
            return response()->json([
                'success' => false,
                'message' => 'User Progress Template feature is not enabled for this studio.',
            ], 403);
        }

        try {
            $hostTemplate = HostProgressTemplate::where('host_id', $host->id)
                ->where('progress_template_id', $progressTemplate->id)
                ->first();

            if ($hostTemplate) {
                $hostTemplate->update([
                    'is_enabled' => false,
                    'deactivated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "{$progressTemplate->name} has been disabled.",
                'is_enabled' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable template. Please try again.',
            ], 500);
        }
    }

    /**
     * Toggle a progress template on/off for the host.
     */
    public function toggle(Request $request, ProgressTemplate $progressTemplate)
    {
        $enable = $request->boolean('enable');

        if ($enable) {
            return $this->enable($request, $progressTemplate);
        } else {
            return $this->disable($request, $progressTemplate);
        }
    }
}
