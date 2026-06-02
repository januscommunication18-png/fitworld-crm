<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\EventAttendee;
use App\Models\Host;
use App\Models\Invoice;
use App\Models\ClassPack;
use App\Models\ClassPlan;
use App\Models\MembershipPlan;
use App\Models\ServicePlan;
use App\Models\Transaction;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MemberPortalController extends Controller
{
    /**
     * Get the authenticated member (client)
     */
    protected function getMember(): Client
    {
        return Auth::guard('member')->user();
    }

    /**
     * Get the host from request attributes
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Member Dashboard
     */
    public function dashboard(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        // Get today's bookings only
        $todayBookings = Booking::where('client_id', $member->id)
            ->where('host_id', $host->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereHas('bookable', function ($q) {
                $q->whereDate('start_time', today());
            })
            ->with(['bookable'])
            ->get()
            ->sortBy(fn($b) => $b->bookable?->start_time);

        // Get recent transactions
        $recentTransactions = Transaction::where('client_id', $member->id)
            ->where('host_id', $host->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Get active memberships
        $activeMemberships = $member->customerMemberships()
            ->where('host_id', $host->id)
            ->where('status', 'active')
            ->with(['membershipPlan', 'membershipPlan.classSessions'])
            ->get();

        // Get active class passes (usable = activated, has credits, not expired/frozen)
        $activeClassPacks = $member->classPassPurchases()
            ->where('host_id', $host->id)
            ->usable()
            ->with('classPass')
            ->get();

        // Get upcoming events this member has registered for (not cancelled).
        $upcomingEvents = $member->eventAttendances()
            ->whereIn('status', [EventAttendee::STATUS_REGISTERED, EventAttendee::STATUS_CONFIRMED, EventAttendee::STATUS_WAITLISTED])
            ->whereHas('event', function ($q) use ($host) {
                $q->where('host_id', $host->id)->where('start_datetime', '>=', now());
            })
            ->with('event')
            ->get()
            ->sortBy(fn($a) => $a->event?->start_datetime)
            ->values();

        // Memberships & class passes bought via manual payment that are still
        // awaiting confirmation — surfaced as "pending" so the member sees them
        // before the studio marks the payment paid (which activates the plan).
        $pendingPlanTransactions = Transaction::where('client_id', $member->id)
            ->where('host_id', $host->id)
            ->whereIn('type', [Transaction::TYPE_MEMBERSHIP_PURCHASE, Transaction::TYPE_CLASS_PACK_PURCHASE])
            ->where('status', Transaction::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();

        // Get pending intake forms
        $pendingIntakeForms = $member->questionnaireResponses()
            ->where('host_id', $host->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->with('version.questionnaire')
            ->get();

        return view('subdomain.member.portal.dashboard', [
            'host' => $host,
            'member' => $member,
            'todayBookings' => $todayBookings,
            'recentTransactions' => $recentTransactions,
            'activeMemberships' => $activeMemberships,
            'activeClassPacks' => $activeClassPacks,
            'upcomingEvents' => $upcomingEvents,
            'pendingPlanTransactions' => $pendingPlanTransactions,
            'pendingIntakeForms' => $pendingIntakeForms,
        ]);
    }

    /**
     * Booking - Browse all classes, services, and memberships
     */
    public function booking(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        // Get member's active memberships
        $activeMemberships = CustomerMembership::where('host_id', $host->id)
            ->where('client_id', $member->id)
            ->active()
            ->notExpired()
            ->withCredits()
            ->with('membershipPlan')
            ->get();

        // Get upcoming class sessions (next 7 days for quick preview)
        $upcomingSessions = ClassSession::where('host_id', $host->id)
            ->where('status', 'published')
            ->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addDays(7))
            ->with(['classPlan', 'primaryInstructor'])
            ->orderBy('start_time')
            ->take(6)
            ->get();

        // Get class plans
        $classPlans = ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get service plans
        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get membership plans
        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get class packs
        $classPacks = ClassPack::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('subdomain.member.portal.booking', [
            'host' => $host,
            'member' => $member,
            'upcomingSessions' => $upcomingSessions,
            'classPlans' => $classPlans,
            'servicePlans' => $servicePlans,
            'membershipPlans' => $membershipPlans,
            'classPacks' => $classPacks,
            'activeMemberships' => $activeMemberships,
        ]);
    }

    /**
     * Schedule - View upcoming classes
     */
    public function schedule(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        // Get member's active memberships
        $activeMemberships = CustomerMembership::where('host_id', $host->id)
            ->where('client_id', $member->id)
            ->active()
            ->notExpired()
            ->withCredits()
            ->with('membershipPlan')
            ->get();

        // Get upcoming class sessions
        $sessions = ClassSession::where('host_id', $host->id)
            ->where('status', 'published')
            ->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addDays(14))
            ->with(['classPlan', 'primaryInstructor', 'room.location'])
            ->orderBy('start_time')
            ->get();

        // Group by date
        $sessionsByDate = $sessions->groupBy(fn($s) => $s->start_time->format('Y-m-d'));

        // Get member's booked session IDs (confirmed bookings only)
        $bookedSessionIds = Booking::where('client_id', $member->id)
            ->where('host_id', $host->id)
            ->where('bookable_type', ClassSession::class)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->pluck('bookable_id')
            ->toArray();

        return view('subdomain.member.portal.schedule', [
            'host' => $host,
            'member' => $member,
            'sessionsByDate' => $sessionsByDate,
            'bookedSessionIds' => $bookedSessionIds,
            'activeMemberships' => $activeMemberships,
        ]);
    }

    /**
     * My Bookings
     */
    public function bookings(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        $filter = $request->get('filter', 'upcoming');

        $query = Booking::where('client_id', $member->id)
            ->where('host_id', $host->id)
            // bookable is polymorphic (ClassSession or ServiceSlot); eager-load
            // each type's own relations so a service booking doesn't trip over
            // class-only relations like classPlan/room.
            ->with(['bookable' => function ($morphTo) {
                $morphTo->morphWith([
                    ClassSession::class => ['classPlan.instructors', 'primaryInstructor', 'room.location'],
                    \App\Models\ServiceSlot::class => ['servicePlan', 'instructor', 'location'],
                ]);
            }]);

        if ($filter === 'upcoming') {
            $query->whereHas('bookable', function ($q) {
                $q->where('start_time', '>=', now());
            });
        } elseif ($filter === 'past') {
            $query->whereHas('bookable', function ($q) {
                $q->where('start_time', '<', now());
            });
        }

        $bookings = $query->orderByDesc('created_at')
            ->paginate(15);

        return view('subdomain.member.portal.bookings', [
            'host' => $host,
            'member' => $member,
            'bookings' => $bookings,
            'filter' => $filter,
        ]);
    }

    /**
     * Cancel a booking from the member portal. Member must own the booking,
     * studio policy must allow cancellations (Booking::canBeCancelled).
     */
    public function cancelBooking(Request $request, string $subdomain, Booking $booking)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        if ($booking->client_id !== $member->id || $booking->host_id !== $host->id) {
            abort(403);
        }

        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'This booking can no longer be cancelled.');
        }

        $reason = (string) $request->input('reason', '');
        // Members aren't Users — there's no User id we can record as the
        // canceller. Stash an actor marker in cancellation_notes so the host
        // dashboard can attribute the cancel to the member rather than
        // misreading null as "Staff (user removed)".
        $notes = 'Cancelled by member: ' . ($member->full_name ?? 'Unknown');
        $booking->cancel($reason !== '' ? $reason : null, $notes, null);

        return back()->with('success', 'Booking cancelled.');
    }

    /**
     * Self check-in for a booking
     */
    public function selfCheckIn(Request $request, string $subdomain, Booking $booking)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        // Verify booking belongs to this member and host
        if ($booking->client_id !== $member->id || $booking->host_id !== $host->id) {
            abort(403);
        }

        $state = $booking->selfCheckInState();
        if (!$state['allowed']) {
            $messages = [
                'disabled' => 'Self check-in is not available at this studio.',
                'not_confirmed' => 'This booking cannot be checked in.',
                'already' => 'You are already checked in.',
                'no_session_time' => 'This booking has no scheduled time.',
                'too_early' => 'Check-in opens '
                    . ($state['opens_at'] ?? now())->diffForHumans(),
                'too_late' => 'Check-in window for this session has closed.',
            ];
            $flashKey = $state['reason'] === 'already' ? 'info' : 'error';
            return back()->with($flashKey, $messages[$state['reason']] ?? 'Check-in is not available right now.');
        }

        $booking->update([
            'checked_in_at' => now(),
            'checked_in_method' => Booking::CHECKIN_SELF ?? 'self',
        ]);

        return back()->with('success', 'Checked in successfully!');
    }

    /**
     * My Payments & Invoices
     */
    public function payments(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        $transactions = Transaction::where('client_id', $member->id)
            ->where('host_id', $host->id)
            ->with(['invoice', 'purchasable'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('subdomain.member.portal.payments', [
            'host' => $host,
            'member' => $member,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Download Invoice PDF
     */
    public function downloadInvoice(Request $request, string $subdomain, Invoice $invoice)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        // Verify invoice belongs to this member and host
        if ($invoice->client_id !== $member->id || $invoice->host_id !== $host->id) {
            abort(404);
        }

        $invoiceService = app(InvoiceService::class);
        return $invoiceService->downloadPdf($invoice);
    }

    /**
     * My Profile
     */
    public function profile(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        return view('subdomain.member.portal.profile', [
            'host' => $host,
            'member' => $member,
        ]);
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email,' . $member->id,
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_profile_photo' => 'nullable|in:1',
        ]);

        $disk = config('filesystems.uploads');

        // Remove existing photo if requested or replaced.
        if (($validated['remove_profile_photo'] ?? null) === '1' || $request->hasFile('profile_photo')) {
            if ($member->profile_photo) {
                try {
                    \Storage::disk($disk)->delete($member->profile_photo);
                } catch (\Throwable $e) {
                    // Ignore — file may live on a different disk or be missing.
                }
            }
            $member->profile_photo = null;
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')
                ->storePublicly($host->getStoragePath('client-photos'), $disk);
            $member->profile_photo = $path;
        }

        $member->fill(collect($validated)->only([
            'first_name', 'last_name', 'email', 'phone', 'date_of_birth',
            'emergency_contact_name', 'emergency_contact_phone',
        ])->all())->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Change Password (for password-based login)
     */
    public function changePassword(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        // Only allow if host uses password authentication
        if ($host->getMemberPortalSetting('login_method') !== 'password') {
            return back()->with('error', 'Password change is not available.');
        }

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $member->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $member->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Memberships - Browse available memberships and class packs to purchase
     */
    public function memberships(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        // Available membership plans to purchase
        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Available class packs to purchase
        $classPackPlans = ClassPack::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Member's active memberships
        $activeMemberships = $member->customerMemberships()
            ->where('host_id', $host->id)
            ->where('status', 'active')
            ->with('membershipPlan')
            ->get();

        // Member's active class passes
        $activeClassPacks = $member->classPassPurchases()
            ->where('host_id', $host->id)
            ->usable()
            ->with('classPass')
            ->get();

        return view('subdomain.member.portal.memberships', [
            'host' => $host,
            'member' => $member,
            'membershipPlans' => $membershipPlans,
            'classPackPlans' => $classPackPlans,
            'activeMemberships' => $activeMemberships,
            'activeClassPacks' => $activeClassPacks,
        ]);
    }

    /**
     * Services - Browse available services
     */
    public function services(Request $request)
    {
        $host = $this->getHost($request);
        $member = $this->getMember();

        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->with('instructors')
            ->orderBy('name')
            ->get();

        return view('subdomain.member.portal.services', [
            'host' => $host,
            'member' => $member,
            'servicePlans' => $servicePlans,
        ]);
    }
}
