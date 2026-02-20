<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\MembershipPlan;
use App\Models\ServicePlan;
use App\Models\StudioGalleryImage;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Get the host from the request attributes (set by ResolveSubdomainHost middleware)
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Display the booking page home/landing
     */
    public function index(Request $request)
    {
        $host = $this->getHost($request);

        // Check if booking page is published
        if (!$host->isBookingPagePublished()) {
            return view('subdomain.not-available', [
                'host' => $host,
            ]);
        }

        // Get upcoming published class sessions
        $upcomingSessions = ClassSession::where('host_id', $host->id)
            ->where('status', 'published')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->with(['classPlan', 'primaryInstructor', 'room.location'])
            ->take(10)
            ->get();

        // Get active instructors
        $instructors = Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get active service plans
        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get active membership plans
        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->where('status', MembershipPlan::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get studio gallery images
        $galleryImages = StudioGalleryImage::where('host_id', $host->id)
            ->ordered()
            ->get();

        // Get booking settings
        $bookingSettings = array_merge(
            Host::defaultBookingSettings(),
            $host->booking_settings ?? []
        );

        // Check if member portal is enabled
        $clientSettings = $host->client_settings ?? [];
        $memberPortalEnabled = $clientSettings['enable_member_portal'] ?? false;

        // Get default location for address display
        $defaultLocation = $host->defaultLocation();

        return view('subdomain.home', [
            'host' => $host,
            'upcomingSessions' => $upcomingSessions,
            'instructors' => $instructors,
            'servicePlans' => $servicePlans,
            'membershipPlans' => $membershipPlans,
            'galleryImages' => $galleryImages,
            'bookingSettings' => $bookingSettings,
            'memberPortalEnabled' => $memberPortalEnabled,
            'defaultLocation' => $defaultLocation,
        ]);
    }

    /**
     * Display the full schedule
     */
    public function schedule(Request $request)
    {
        $host = $this->getHost($request);

        // Get class sessions for the next 30 days
        $sessions = ClassSession::where('host_id', $host->id)
            ->where('status', 'published')
            ->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addDays(30))
            ->orderBy('start_time')
            ->with(['classPlan', 'primaryInstructor', 'room.location'])
            ->get()
            ->groupBy(fn($session) => $session->start_time->format('Y-m-d'));

        // Get booking settings
        $bookingSettings = array_merge(
            Host::defaultBookingSettings(),
            $host->booking_settings ?? []
        );

        return view('subdomain.schedule', [
            'host' => $host,
            'sessionsByDate' => $sessions,
            'bookingSettings' => $bookingSettings,
        ]);
    }

    /**
     * Display class session details
     */
    public function classDetails(Request $request, string $subdomain, ClassSession $classSession)
    {
        $host = $this->getHost($request);

        // Ensure the class belongs to this studio
        if ($classSession->host_id !== $host->id) {
            abort(404);
        }

        $classSession->load(['classPlan', 'primaryInstructor', 'room.location']);

        return view('subdomain.class-details', [
            'host' => $host,
            'session' => $classSession,
        ]);
    }

    /**
     * Display all instructors
     */
    public function instructors(Request $request)
    {
        $host = $this->getHost($request);

        $instructors = Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('subdomain.instructors', [
            'host' => $host,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Display an instructor's profile
     */
    public function instructorProfile(Request $request, string $subdomain, Instructor $instructor)
    {
        $host = $this->getHost($request);

        // Ensure the instructor belongs to this studio
        if ($instructor->host_id !== $host->id) {
            abort(404);
        }

        // Get instructor's upcoming classes
        $upcomingSessions = ClassSession::where('host_id', $host->id)
            ->where('primary_instructor_id', $instructor->id)
            ->where('status', 'published')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->with(['classPlan', 'room.location'])
            ->take(10)
            ->get();

        return view('subdomain.instructor-profile', [
            'host' => $host,
            'instructor' => $instructor,
            'upcomingSessions' => $upcomingSessions,
        ]);
    }
}
