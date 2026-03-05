<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\ClientProgressPhoto;
use App\Models\ClientProgressReport;
use App\Models\ClientProgressValue;
use App\Models\ProgressTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassSessionProgressController extends Controller
{
    /**
     * Show the batch progress recording form for a class session.
     */
    public function create(Request $request, ClassSession $classSession, ProgressTemplate $progressTemplate)
    {
        $this->authorizeHost($classSession);

        $host = auth()->user()->host;

        // Check if host has the progress-templates feature
        if (!$host->hasFeature('progress-templates')) {
            abort(403, 'Progress Templates feature is not enabled.');
        }

        // Verify this template is attached to the class plan
        if (!$classSession->classPlan->progressTemplates->contains($progressTemplate)) {
            abort(404, 'This progress template is not attached to this class.');
        }

        // Get confirmed bookings with clients
        $bookingsQuery = $classSession->confirmedBookings()->with('client');

        // If a specific client is requested, filter to only that client
        $singleClientMode = false;
        $singleClient = null;
        if ($request->has('client')) {
            $clientId = $request->input('client');
            $bookingsQuery->where('client_id', $clientId);
            $singleClientMode = true;
            $singleClient = \App\Models\Client::find($clientId);
        }

        $bookings = $bookingsQuery->get();

        // Load template with sections and metrics
        $progressTemplate->load(['sections.metrics' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        // Get existing progress reports for this session/template combination, keyed by booking_id
        $existingReports = ClientProgressReport::where('class_session_id', $classSession->id)
            ->where('progress_template_id', $progressTemplate->id)
            ->with(['values', 'photos'])
            ->get()
            ->keyBy('booking_id');

        return view('host.class-sessions.record-progress', compact(
            'classSession',
            'progressTemplate',
            'bookings',
            'existingReports',
            'singleClientMode',
            'singleClient'
        ));
    }

    /**
     * Store batch progress reports for all attendees.
     */
    public function store(Request $request, ClassSession $classSession, ProgressTemplate $progressTemplate)
    {
        $this->authorizeHost($classSession);

        $host = auth()->user()->host;

        // Check if host has the progress-templates feature
        if (!$host->hasFeature('progress-templates')) {
            abort(403, 'Progress Templates feature is not enabled.');
        }

        // Verify this template is attached to the class plan
        if (!$classSession->classPlan->progressTemplates->contains($progressTemplate)) {
            abort(404, 'This progress template is not attached to this class.');
        }

        $reports = $request->input('reports', []);

        if (empty($reports)) {
            return back()->with('error', 'No progress data provided.');
        }

        $savedCount = 0;

        foreach ($reports as $bookingId => $reportData) {
            // Skip if not enabled for this booking
            if (empty($reportData['enabled'])) {
                continue;
            }

            $booking = Booking::where('id', $bookingId)
                ->where('bookable_id', $classSession->id)
                ->where('bookable_type', ClassSession::class)
                ->first();

            if (!$booking) {
                continue;
            }

            // Create or update the progress report
            $report = ClientProgressReport::updateOrCreate(
                [
                    'host_id' => $host->id,
                    'client_id' => $booking->client_id,
                    'progress_template_id' => $progressTemplate->id,
                    'booking_id' => $bookingId,
                    'class_session_id' => $classSession->id,
                ],
                [
                    'report_date' => $classSession->start_time->toDateString(),
                    'recorded_by_user_id' => auth()->id(),
                    'trainer_notes' => $reportData['trainer_notes'] ?? null,
                    'status' => ClientProgressReport::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]
            );

            // Save metric values
            if (!empty($reportData['metrics'])) {
                foreach ($reportData['metrics'] as $metricId => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }

                    $metric = $progressTemplate->sections
                        ->flatMap(fn($s) => $s->metrics)
                        ->firstWhere('id', $metricId);

                    if (!$metric) {
                        continue;
                    }

                    $valueData = [
                        'progress_template_metric_id' => $metricId,
                        'recorded_at' => now(),
                    ];

                    // Determine value type based on metric type
                    if (in_array($metric->metric_type, ['slider', 'number', 'rating'])) {
                        $valueData['value_numeric'] = (float) $value;
                    } elseif (in_array($metric->metric_type, ['checkbox_list'])) {
                        $valueData['value_json'] = is_array($value) ? $value : [$value];
                    } else {
                        $valueData['value_text'] = (string) $value;
                    }

                    ClientProgressValue::updateOrCreate(
                        [
                            'client_progress_report_id' => $report->id,
                            'progress_template_metric_id' => $metricId,
                        ],
                        $valueData
                    );
                }
            }

            // Handle photo uploads
            if ($request->hasFile("reports.{$bookingId}.photos.before")) {
                $this->saveProgressPhoto(
                    $report,
                    $request->file("reports.{$bookingId}.photos.before"),
                    ClientProgressPhoto::TYPE_BEFORE
                );
            }

            if ($request->hasFile("reports.{$bookingId}.photos.after")) {
                $this->saveProgressPhoto(
                    $report,
                    $request->file("reports.{$bookingId}.photos.after"),
                    ClientProgressPhoto::TYPE_AFTER
                );
            }

            // Calculate overall score
            $report->calculateOverallScore();

            $savedCount++;
        }

        if ($savedCount === 0) {
            return back()->with('warning', 'No progress reports were saved. Please fill in at least one metric.');
        }

        return redirect()->route('class-sessions.show', $classSession)
            ->with('success', "Progress recorded for {$savedCount} client(s).");
    }

    /**
     * Store progress report for a single client from class session view.
     */
    public function storeSingle(Request $request, ClassSession $classSession, Booking $booking, ProgressTemplate $progressTemplate)
    {
        $this->authorizeHost($classSession);

        $host = auth()->user()->host;

        // Check if host has the progress-templates feature
        if (!$host->hasFeature('progress-templates')) {
            abort(403, 'Progress Templates feature is not enabled.');
        }

        // Verify booking belongs to this session
        if ($booking->bookable_id !== $classSession->id || $booking->bookable_type !== ClassSession::class) {
            abort(404);
        }

        $request->validate([
            'metrics' => 'array',
            'trainer_notes' => 'nullable|string|max:5000',
        ]);

        // Create or update the progress report
        $report = ClientProgressReport::updateOrCreate(
            [
                'host_id' => $host->id,
                'client_id' => $booking->client_id,
                'progress_template_id' => $progressTemplate->id,
                'booking_id' => $booking->id,
                'class_session_id' => $classSession->id,
            ],
            [
                'report_date' => $classSession->start_time->toDateString(),
                'recorded_by_user_id' => auth()->id(),
                'trainer_notes' => $request->input('trainer_notes'),
                'status' => ClientProgressReport::STATUS_COMPLETED,
                'completed_at' => now(),
            ]
        );

        // Save metric values
        $metrics = $request->input('metrics', []);
        foreach ($metrics as $metricId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $report->setValueForMetric($metricId, $value);
        }

        // Calculate overall score
        $report->calculateOverallScore();

        return response()->json([
            'success' => true,
            'message' => 'Progress recorded successfully.',
            'report_id' => $report->id,
        ]);
    }

    /**
     * Authorize that the class session belongs to the current host.
     */
    private function authorizeHost(ClassSession $classSession): void
    {
        if ($classSession->host_id !== auth()->user()->host_id) {
            abort(403);
        }
    }

    /**
     * Save a progress photo for a report.
     */
    private function saveProgressPhoto(ClientProgressReport $report, $file, string $type): void
    {
        $host = auth()->user()->host;
        $disk = config('filesystems.uploads');

        // Generate path: progress-photos/{host_id}/{client_id}/{filename}
        $path = sprintf(
            'progress-photos/%d/%d/%s_%s.%s',
            $host->id,
            $report->client_id,
            $type,
            now()->format('Y-m-d_His'),
            $file->getClientOriginalExtension()
        );

        // Store the file
        Storage::disk($disk)->put($path, file_get_contents($file));

        // Delete existing photo of this type for this report
        $existingPhoto = ClientProgressPhoto::where('client_progress_report_id', $report->id)
            ->where('photo_type', $type)
            ->first();

        if ($existingPhoto) {
            $existingPhoto->delete();
        }

        // Create the photo record
        ClientProgressPhoto::create([
            'client_progress_report_id' => $report->id,
            'photo_type' => $type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'sort_order' => $type === ClientProgressPhoto::TYPE_BEFORE ? 1 : 2,
            'is_private' => false,
        ]);
    }
}
