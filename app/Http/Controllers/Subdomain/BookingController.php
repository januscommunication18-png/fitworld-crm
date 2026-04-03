<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\ClassPass;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\Event;
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

        // Get upcoming published events
        $upcomingEvents = Event::forHost($host->id)
            ->published()
            ->upcoming()
            ->where('visibility', 'public')
            ->withCount('registeredAttendees')
            ->orderBy('start_datetime')
            ->take(6)
            ->get();

        // Get active instructors
        $instructors = Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get active service plans (visible on booking page)
        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->where('is_visible_on_booking_page', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get active membership plans (visible publicly)
        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->where('status', MembershipPlan::STATUS_ACTIVE)
            ->where('visibility_public', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get active class plans (visible on booking page)
        $classPlans = ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->where('is_visible_on_booking_page', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get active class passes (visible publicly)
        $classPasses = ClassPass::where('host_id', $host->id)
            ->visible()
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
            'upcomingEvents' => $upcomingEvents,
            'instructors' => $instructors,
            'servicePlans' => $servicePlans,
            'membershipPlans' => $membershipPlans,
            'classPlans' => $classPlans,
            'classPasses' => $classPasses,
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
     * Display event details
     */
    public function eventDetails(Request $request, string $subdomain, Event $event)
    {
        $host = $this->getHost($request);

        // Ensure the event belongs to this studio and is public
        if ($event->host_id !== $host->id || $event->visibility !== 'public') {
            abort(404);
        }

        // Only show published events
        if ($event->status !== Event::STATUS_PUBLISHED) {
            abort(404);
        }

        $event->loadCount('registeredAttendees');

        return view('subdomain.event-details', [
            'host' => $host,
            'event' => $event,
        ]);
    }

    /**
     * Register for an event (public registration)
     */
    public function registerForEvent(Request $request, string $subdomain, Event $event)
    {
        $host = $this->getHost($request);

        // Ensure the event belongs to this studio and is public
        if ($event->host_id !== $host->id || $event->visibility !== 'public') {
            abort(404);
        }

        // Only allow registration for published, upcoming events
        if ($event->status !== Event::STATUS_PUBLISHED || $event->start_datetime < now()) {
            return back()->with('error', 'This event is no longer accepting registrations.');
        }

        // Check capacity
        $currentCount = $event->registeredAttendees()->count();
        if ($event->capacity && $currentCount >= $event->capacity) {
            return back()->with('error', 'Sorry, this event is full.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Find or create client
        $client = \App\Models\Client::where('host_id', $host->id)
            ->where('email', $validated['email'])
            ->first();

        if (!$client) {
            $client = \App\Models\Client::create([
                'host_id' => $host->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => 'active',
                'source' => 'event_registration',
            ]);
        }

        // Check if already registered
        $existingAttendee = \App\Models\EventAttendee::where('event_id', $event->id)
            ->where('client_id', $client->id)
            ->first();

        if ($existingAttendee) {
            return back()->with('error', 'You are already registered for this event.');
        }

        // Register the attendee
        \App\Models\EventAttendee::create([
            'event_id' => $event->id,
            'client_id' => $client->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        return redirect()->route('subdomain.event', [
            'subdomain' => $host->subdomain,
            'event' => $event->id
        ])->with('success', 'You have been registered for this event! We\'ll send you a confirmation email.');
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

        // Load certifications for public display
        $instructor->load('studioCertifications');

        // Load booking profile if exists and is accepting bookings
        $bookingProfile = \App\Models\BookingProfile::where('host_id', $host->id)
            ->where('instructor_id', $instructor->id)
            ->where('is_enabled', true)
            ->where('is_setup_complete', true)
            ->first();

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
            'bookingProfile' => $bookingProfile,
        ]);
    }

    /**
     * Set the user's preferred currency in session
     */
    public function setCurrency(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'currency' => 'required|string|size:3',
        ]);

        $currency = strtoupper($validated['currency']);

        // Verify this currency is accepted by the host
        $hostCurrencies = $host->currencies ?? [$host->default_currency ?? 'USD'];
        if (!in_array($currency, $hostCurrencies)) {
            return response()->json([
                'success' => false,
                'message' => 'This currency is not accepted.',
            ], 400);
        }

        // Store in session with host-specific key
        $request->session()->put("currency_{$host->id}", $currency);

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'symbol' => MembershipPlan::getCurrencySymbol($currency),
        ]);
    }

    /**
     * Get the user's preferred currency from session
     */
    public function getCurrency(Request $request)
    {
        $host = $this->getHost($request);

        $currency = $request->session()->get("currency_{$host->id}", $host->default_currency ?? 'USD');
        $hostCurrencies = $host->currencies ?? [$host->default_currency ?? 'USD'];

        return response()->json([
            'currency' => $currency,
            'symbol' => MembershipPlan::getCurrencySymbol($currency),
            'available_currencies' => $hostCurrencies,
        ]);
    }

    /**
     * Get selected currency for a host (static helper)
     */
    public static function getSelectedCurrency(Request $request, Host $host): string
    {
        return $request->session()->get("currency_{$host->id}", $host->default_currency ?? 'USD');
    }

    /**
     * Set the user's preferred language in session
     */
    public function setLanguage(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'language' => 'required|string|size:2',
        ]);

        $language = strtolower($validated['language']);

        // Get available languages for this host
        $hostLanguages = $host->studio_languages ?? [$host->default_language_booking ?? 'en'];
        if (!is_array($hostLanguages) || empty($hostLanguages)) {
            $hostLanguages = ['en'];
        }

        // Verify this language is available for the host
        if (!in_array($language, $hostLanguages)) {
            return response()->json([
                'success' => false,
                'message' => 'This language is not available.',
            ], 400);
        }

        // Store in session with host-specific key
        $request->session()->put("language_{$host->id}", $language);

        return response()->json([
            'success' => true,
            'language' => $language,
            'name' => self::getLanguageName($language),
        ]);
    }

    /**
     * Get the user's preferred language from session
     */
    public function getLanguage(Request $request)
    {
        $host = $this->getHost($request);

        $language = $request->session()->get("language_{$host->id}", $host->default_language_booking ?? 'en');
        $hostLanguages = $host->studio_languages ?? [$host->default_language_booking ?? 'en'];
        if (!is_array($hostLanguages) || empty($hostLanguages)) {
            $hostLanguages = ['en'];
        }

        return response()->json([
            'language' => $language,
            'name' => self::getLanguageName($language),
            'available_languages' => $hostLanguages,
        ]);
    }

    /**
     * Get selected language for a host (static helper)
     */
    public static function getSelectedLanguage(Request $request, Host $host): string
    {
        return $request->session()->get("language_{$host->id}", $host->default_language_booking ?? 'en');
    }

    /**
     * Get language name from code
     */
    public static function getLanguageName(string $code): string
    {
        $languages = [
            'en' => 'English',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'es' => 'Español',
        ];

        return $languages[$code] ?? $code;
    }

    /**
     * Get language flag from code
     */
    public static function getLanguageFlag(string $code): string
    {
        $flags = [
            'en' => '🇺🇸',
            'fr' => '🇫🇷',
            'de' => '🇩🇪',
            'es' => '🇪🇸',
        ];

        return $flags[$code] ?? '🌐';
    }
}
