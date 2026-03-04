<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientProgressReport;
use Illuminate\Http\Request;

class ClientProgressController extends Controller
{
    /**
     * Display progress history for a client.
     */
    public function index(int $client)
    {
        $client = Client::findOrFail($client);
        $this->authorizeClient($client);

        $host = auth()->user()->host;

        // Check if host has the progress-templates feature
        if (!$host->hasFeature('progress-templates')) {
            abort(403, 'Progress Templates feature is not enabled.');
        }

        // Get all progress reports for this client with related data
        $progressReports = $client->progressReports()
            ->with([
                'template',
                'classSession.classPlan',
                'recordedBy',
                'values.metric.section',
            ])
            ->orderBy('report_date', 'desc')
            ->get();

        // Group reports by template for summary stats
        $reportsByTemplate = $progressReports->groupBy('progress_template_id');

        // Calculate progress trends per template
        $templateStats = [];
        foreach ($reportsByTemplate as $templateId => $reports) {
            $template = $reports->first()->template;
            $scores = $reports->pluck('overall_score')->filter()->values();

            $templateStats[$templateId] = [
                'template' => $template,
                'total_reports' => $reports->count(),
                'latest_score' => $scores->first(),
                'average_score' => $scores->avg(),
                'trend' => $this->calculateTrend($scores),
            ];
        }

        return view('host.clients.progress.index', compact(
            'client',
            'progressReports',
            'templateStats'
        ));
    }

    /**
     * Display a specific progress report.
     */
    public function show(int $client, int $clientProgressReport)
    {
        $client = Client::findOrFail($client);
        $this->authorizeClient($client);

        $report = ClientProgressReport::findOrFail($clientProgressReport);

        $host = auth()->user()->host;

        // Check if host has the progress-templates feature
        if (!$host->hasFeature('progress-templates')) {
            abort(403, 'Progress Templates feature is not enabled.');
        }

        // Verify report belongs to this client
        if ($report->client_id !== $client->id) {
            abort(404);
        }

        // Load all related data
        $report->load([
            'template.sections.metrics',
            'values.metric.section',
            'classSession.classPlan',
            'recordedBy',
            'photos',
            'measurements',
        ]);

        // Get previous and next reports for navigation
        $previousReport = ClientProgressReport::where('client_id', $client->id)
            ->where('progress_template_id', $report->progress_template_id)
            ->where('report_date', '<', $report->report_date)
            ->orderBy('report_date', 'desc')
            ->first();

        $nextReport = ClientProgressReport::where('client_id', $client->id)
            ->where('progress_template_id', $report->progress_template_id)
            ->where('report_date', '>', $report->report_date)
            ->orderBy('report_date', 'asc')
            ->first();

        // Get all reports for this template to show history chart
        $templateReports = ClientProgressReport::where('client_id', $client->id)
            ->where('progress_template_id', $report->progress_template_id)
            ->orderBy('report_date', 'asc')
            ->get(['id', 'report_date', 'overall_score']);

        // Organize values by section for display
        $valuesBySection = [];
        foreach ($report->template->sections as $section) {
            $valuesBySection[$section->id] = [
                'section' => $section,
                'metrics' => [],
            ];

            foreach ($section->metrics as $metric) {
                $value = $report->values->where('progress_template_metric_id', $metric->id)->first();
                $valuesBySection[$section->id]['metrics'][] = [
                    'metric' => $metric,
                    'value' => $value,
                ];
            }
        }

        return view('host.clients.progress.show', compact(
            'client',
            'report',
            'previousReport',
            'nextReport',
            'templateReports',
            'valuesBySection'
        ));
    }

    /**
     * Calculate trend direction from scores.
     */
    private function calculateTrend($scores): string
    {
        if ($scores->count() < 2) {
            return 'neutral';
        }

        $recent = $scores->take(3)->avg();
        $older = $scores->skip(3)->take(3)->avg();

        if ($older === null) {
            return 'neutral';
        }

        $diff = $recent - $older;

        if ($diff > 2) {
            return 'up';
        } elseif ($diff < -2) {
            return 'down';
        }

        return 'neutral';
    }

    /**
     * Authorize that the client belongs to the current host.
     */
    private function authorizeClient(Client $client): void
    {
        if ($client->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }
}
