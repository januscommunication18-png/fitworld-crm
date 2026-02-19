<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
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

        // Get upcoming bookings
        $upcomingBookings = Booking::where('client_id', $member->id)
            ->where('host_id', $host->id)
            ->whereHas('bookable', function ($q) {
                $q->where('start_time', '>=', now());
            })
            ->with(['bookable'])
            ->take(5)
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
            ->with('membershipPlan')
            ->get();

        // Get active class packs (usable = has credits + not expired)
        $activeClassPacks = $member->classPackPurchases()
            ->where('host_id', $host->id)
            ->usable()
            ->with('classPack')
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
            'upcomingBookings' => $upcomingBookings,
            'recentTransactions' => $recentTransactions,
            'activeMemberships' => $activeMemberships,
            'activeClassPacks' => $activeClassPacks,
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
            ->with(['bookable']);

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
        ]);

        $member->update($validated);

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

        // Member's active class packs
        $activeClassPacks = $member->classPackPurchases()
            ->where('host_id', $host->id)
            ->usable()
            ->with('classPack')
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
