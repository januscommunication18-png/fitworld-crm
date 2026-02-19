<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\ClassPack;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\Host;
use App\Models\CustomerMembership;
use App\Models\MembershipPlan;
use App\Models\ServicePlan;
use App\Models\ServiceSlot;
use App\Services\BookingFlowService;
use App\Services\TransactionService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class BookingFlowController extends Controller
{
    protected BookingFlowService $bookingService;
    protected TransactionService $transactionService;

    public function __construct(BookingFlowService $bookingService, TransactionService $transactionService)
    {
        $this->bookingService = $bookingService;
        $this->transactionService = $transactionService;
    }

    /**
     * Get the host from the request attributes
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Step 1: Select booking type - Redirect to member portal booking page
     */
    public function selectType(Request $request)
    {
        $host = $this->getHost($request);

        // Check if user already has something selected, go to contact
        $bookingState = $this->bookingService->getState($request);
        if (!empty($bookingState['selected_item'])) {
            return redirect()->route('booking.contact', ['subdomain' => $host->subdomain]);
        }

        // Redirect to member portal booking page to browse options
        return redirect()->route('member.portal.booking', ['subdomain' => $host->subdomain]);
    }

    /**
     * Step 1a: Select a specific class session
     */
    public function selectClass(Request $request)
    {
        $host = $this->getHost($request);
        $classPlanId = $request->route('classPlanId');
        $classPlanId = $classPlanId ? (int) $classPlanId : null;

        // Get class plans for filter
        $classPlans = ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Build sessions query
        $sessionsQuery = ClassSession::where('host_id', $host->id)
            ->where('status', 'published')
            ->where('start_time', '>=', now())
            ->with(['classPlan', 'primaryInstructor', 'room.location'])
            ->orderBy('start_time');

        if ($classPlanId) {
            $sessionsQuery->where('class_plan_id', $classPlanId);
        }

        $sessions = $sessionsQuery->take(50)->get();

        // Group by date for easier display
        $sessionsByDate = $sessions->groupBy(function ($session) {
            return $session->start_time->format('Y-m-d');
        });

        return view('subdomain.booking.select-class', [
            'host' => $host,
            'classPlans' => $classPlans,
            'sessions' => $sessions,
            'sessionsByDate' => $sessionsByDate,
            'selectedPlanId' => $classPlanId,
        ]);
    }

    /**
     * Select a specific class session and proceed
     */
    public function selectClassSession(Request $request)
    {
        $host = $this->getHost($request);

        // Resolve session model from route parameter
        $sessionId = $request->route('session');
        $session = ClassSession::where('id', $sessionId)
            ->where('host_id', $host->id)
            ->firstOrFail();

        // Check if session is bookable
        if ($session->status !== 'published' || $session->start_time < now()) {
            return back()->with('error', 'This class is no longer available for booking.');
        }

        // Check capacity
        if ($session->is_full && !$session->allow_waitlist) {
            return back()->with('error', 'This class is full.');
        }

        // Check if logged-in member has an active membership covering this class
        $member = Auth::guard('member')->user();
        $applicableMembership = null;
        $price = $session->price ?? $session->classPlan?->drop_in_price ?? 0;
        $usingMembership = false;

        if ($member && $session->classPlan) {
            $applicableMembership = $this->findApplicableMembershipForClass($host, $member, $session->classPlan);

            if ($applicableMembership) {
                $price = 0; // Free with membership
                $usingMembership = true;
            }
        }

        // Store in booking state
        $this->bookingService->setBookingType($request, 'class_session');
        $this->bookingService->setSelectedItem($request, [
            'type' => 'class_session',
            'id' => $session->id,
            'name' => $session->display_title,
            'price' => $price,
            'original_price' => $session->price ?? $session->classPlan?->drop_in_price ?? 0,
            'datetime' => $session->start_time->format('M j, Y g:i A'),
            'instructor' => $session->primaryInstructor?->name,
            'location' => $session->room?->location?->name,
            'is_waitlist' => $session->is_full,
            'using_membership' => $usingMembership,
            'membership_id' => $applicableMembership?->id,
            'membership_name' => $applicableMembership?->membershipPlan?->name,
        ]);

        return redirect()->route('booking.contact', ['subdomain' => $host->subdomain]);
    }

    /**
     * Find an applicable membership for a class plan
     */
    protected function findApplicableMembershipForClass(Host $host, Client $client, ClassPlan $classPlan): ?CustomerMembership
    {
        // Get all active memberships for this client at this host
        $memberships = CustomerMembership::where('host_id', $host->id)
            ->where('client_id', $client->id)
            ->active()
            ->notExpired()
            ->withCredits()
            ->with('membershipPlan')
            ->get();

        // Find one that covers this class plan
        foreach ($memberships as $membership) {
            if ($membership->canUseForClassPlan($classPlan) && $membership->hasAvailableCredits()) {
                return $membership;
            }
        }

        return null;
    }

    /**
     * Step 1b: Select a service and available slot
     */
    public function selectService(Request $request)
    {
        $host = $this->getHost($request);
        $servicePlanId = $request->route('servicePlanId');
        $servicePlanId = $servicePlanId ? (int) $servicePlanId : null;

        // Get service plans
        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // If a plan is selected, get available slots
        $slots = collect();
        $selectedPlan = null;

        if ($servicePlanId) {
            $selectedPlan = ServicePlan::where('host_id', $host->id)
                ->where('id', $servicePlanId)
                ->first();

            if ($selectedPlan) {
                $slots = ServiceSlot::where('host_id', $host->id)
                    ->where('service_plan_id', $servicePlanId)
                    ->where('status', 'available')
                    ->where('start_time', '>=', now())
                    ->with(['instructor', 'location'])
                    ->orderBy('start_time')
                    ->take(30)
                    ->get();
            }
        }

        // Group slots by date
        $slotsByDate = $slots->groupBy(function ($slot) {
            return $slot->start_time->format('Y-m-d');
        });

        return view('subdomain.booking.select-service', [
            'host' => $host,
            'servicePlans' => $servicePlans,
            'slots' => $slots,
            'slotsByDate' => $slotsByDate,
            'selectedPlan' => $selectedPlan,
            'selectedPlanId' => $servicePlanId,
        ]);
    }

    /**
     * Select a specific service slot and proceed
     */
    public function selectServiceSlot(Request $request)
    {
        $host = $this->getHost($request);

        // Resolve slot model from route parameter
        $slotId = $request->route('slot');
        $slot = ServiceSlot::where('id', $slotId)
            ->where('host_id', $host->id)
            ->firstOrFail();

        // Check if slot is available
        if ($slot->status !== 'available' || $slot->start_time < now()) {
            return back()->with('error', 'This time slot is no longer available.');
        }

        // Store in booking state
        $this->bookingService->setBookingType($request, 'service_slot');
        $this->bookingService->setSelectedItem($request, [
            'type' => 'service_slot',
            'id' => $slot->id,
            'name' => $slot->servicePlan?->name ?? 'Service',
            'price' => $slot->price ?? $slot->servicePlan?->price ?? 0,
            'datetime' => $slot->start_time->format('M j, Y g:i A'),
            'duration' => $slot->duration_minutes ?? $slot->servicePlan?->duration_minutes,
            'instructor' => $slot->instructor?->name,
            'location' => $slot->location?->name,
        ]);

        return redirect()->route('booking.contact', ['subdomain' => $host->subdomain]);
    }

    /**
     * Step 1c: Select a membership plan or class pack
     */
    public function selectMembership(Request $request)
    {
        $host = $this->getHost($request);

        $membershipPlans = MembershipPlan::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $classPacks = ClassPack::where('host_id', $host->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('subdomain.booking.select-membership', [
            'host' => $host,
            'membershipPlans' => $membershipPlans,
            'classPacks' => $classPacks,
        ]);
    }

    /**
     * Select a membership plan and proceed
     */
    public function selectMembershipPlan(Request $request)
    {
        $host = $this->getHost($request);

        // Resolve plan model from route parameter
        $planId = $request->route('plan');
        $plan = MembershipPlan::where('id', $planId)
            ->where('host_id', $host->id)
            ->where('status', 'active')
            ->firstOrFail();

        $this->bookingService->setBookingType($request, 'membership_plan');
        $this->bookingService->setSelectedItem($request, [
            'type' => 'membership_plan',
            'id' => $plan->id,
            'name' => $plan->name,
            'price' => $plan->price,
            'billing_period' => $plan->interval === 'monthly' ? 'per month' : 'per year',
            'interval' => $plan->interval,
            'membership_type' => $plan->type, // unlimited or credits
            'credits_per_cycle' => $plan->credits_per_cycle,
            'description' => $plan->description,
        ]);

        return redirect()->route('booking.contact', ['subdomain' => $host->subdomain]);
    }

    /**
     * Select a class pack and proceed
     */
    public function selectClassPack(Request $request)
    {
        $host = $this->getHost($request);

        // Resolve pack model from route parameter
        $packId = $request->route('pack');
        $pack = ClassPack::where('id', $packId)
            ->where('host_id', $host->id)
            ->where('status', 'active')
            ->firstOrFail();

        $this->bookingService->setBookingType($request, 'class_pack');
        $this->bookingService->setSelectedItem($request, [
            'type' => 'class_pack',
            'id' => $pack->id,
            'name' => $pack->name,
            'price' => $pack->price,
            'class_count' => $pack->class_count,
            'validity_days' => $pack->validity_days,
        ]);

        return redirect()->route('booking.contact', ['subdomain' => $host->subdomain]);
    }

    /**
     * Step 2: Contact Information
     */
    public function contactInfo(Request $request)
    {
        $host = $this->getHost($request);
        $bookingState = $this->bookingService->getState($request);

        // Ensure we have a selected item
        if (empty($bookingState['selected_item'])) {
            return redirect()->route('booking.select-type', ['subdomain' => $host->subdomain])
                ->with('error', 'Please select what you would like to book first.');
        }

        // Pre-fill from logged in member if available
        $member = Auth::guard('member')->user();
        $prefillData = [];

        if ($member) {
            $prefillData = [
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
            ];
        }

        return view('subdomain.booking.contact-info', [
            'host' => $host,
            'bookingState' => $bookingState,
            'prefillData' => $prefillData,
            'isLoggedIn' => (bool) $member,
        ]);
    }

    /**
     * Save contact information
     */
    public function saveContactInfo(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
        ]);

        // Store contact info in session
        $this->bookingService->setContactInfo($request, $validated);

        return redirect()->route('booking.payment', ['subdomain' => $host->subdomain]);
    }

    /**
     * Step 3: Terms & Payment Selection
     */
    public function payment(Request $request)
    {
        $host = $this->getHost($request);
        $bookingState = $this->bookingService->getState($request);

        // Ensure we have contact info
        if (empty($bookingState['contact_info'])) {
            return redirect()->route('booking.contact', ['subdomain' => $host->subdomain]);
        }

        $selectedItem = $bookingState['selected_item'] ?? [];
        $usingMembership = $selectedItem['using_membership'] ?? false;

        // If using membership, no payment selection needed
        $enabledMethods = [];
        if ($usingMembership) {
            $enabledMethods[] = [
                'id' => 'membership',
                'name' => 'Use Membership',
                'icon' => 'id-badge-2',
                'description' => 'Included in your ' . ($selectedItem['membership_name'] ?? 'membership'),
                'is_membership' => true,
            ];
        } else {
            // Get enabled payment methods from host settings
            $paymentSettings = $host->payment_settings ?? [];

            // Check if Stripe is connected and cards are accepted
            $acceptCards = $paymentSettings['accept_cards'] ?? true;
            if (!empty($host->stripe_account_id) && $acceptCards) {
                $enabledMethods[] = [
                    'id' => 'stripe',
                    'name' => 'Credit/Debit Card',
                    'icon' => 'credit-card',
                    'description' => 'Pay securely with your card',
                ];
            }

            // Manual payment methods from the new structure
            $manualMethodsConfig = $paymentSettings['manual_methods'] ?? [];
            $manualMethodLabels = [
                'venmo' => 'Venmo',
                'zelle' => 'Zelle',
                'cash_app' => 'Cash App',
                'paypal' => 'PayPal',
                'bank_transfer' => 'Bank Transfer',
                'cash' => 'Cash (Pay at Studio)',
            ];

            foreach ($manualMethodLabels as $methodKey => $methodName) {
                $methodConfig = $manualMethodsConfig[$methodKey] ?? [];
                if (!empty($methodConfig['enabled'])) {
                    $enabledMethods[] = [
                        'id' => $methodKey,
                        'name' => $methodName,
                        'icon' => $this->getPaymentMethodIcon($methodKey),
                        'description' => $methodConfig['instructions'] ?? null,
                    ];
                }
            }

            // If no payment methods, allow cash as default
            if (empty($enabledMethods)) {
                $enabledMethods[] = [
                    'id' => 'cash',
                    'name' => 'Pay at Studio',
                    'icon' => 'cash',
                    'description' => 'Pay when you arrive',
                ];
            }
        }

        // Get terms URL
        $termsUrl = $host->getPolicy('liability_waiver_url') ?? null;

        return view('subdomain.booking.payment', [
            'host' => $host,
            'bookingState' => $bookingState,
            'paymentMethods' => $enabledMethods,
            'termsUrl' => $termsUrl,
            'usingMembership' => $usingMembership,
        ]);
    }

    /**
     * Get icon for payment method
     */
    protected function getPaymentMethodIcon(string $method): string
    {
        return match ($method) {
            'stripe' => 'credit-card',
            'cash' => 'cash-banknote',
            'venmo' => 'brand-venmo',
            'zelle' => 'cash',
            'paypal' => 'brand-paypal',
            'cash_app' => 'cash',
            'bank_transfer' => 'building-bank',
            default => 'wallet',
        };
    }

    /**
     * Clear booking state and start over
     */
    public function clear(Request $request)
    {
        $host = $this->getHost($request);
        $this->bookingService->clearState($request);

        return redirect()->route('booking.select-type', ['subdomain' => $host->subdomain]);
    }

    /**
     * Process payment submission
     */
    public function processPayment(Request $request)
    {
        $host = $this->getHost($request);
        $bookingState = $this->bookingService->getState($request);

        // Validate booking state
        if (empty($bookingState['selected_item']) || empty($bookingState['contact_info'])) {
            return redirect()->route('booking.select-type', ['subdomain' => $host->subdomain])
                ->with('error', 'Your booking session has expired. Please start again.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
            'terms_accepted' => 'sometimes|accepted',
        ]);

        $paymentMethod = $validated['payment_method'];

        // Get or create client
        $client = $this->bookingService->getOrCreateClient($request, $host);
        if (!$client) {
            return back()->with('error', 'Unable to process your booking. Please try again.');
        }

        $selectedItem = $bookingState['selected_item'];

        // Handle membership-based booking
        if ($paymentMethod === 'membership') {
            return $this->processMembershipBooking($request, $host, $client, $selectedItem);
        }

        // Determine if this is Stripe or manual payment
        $isStripe = $paymentMethod === 'stripe';
        $manualMethod = $isStripe ? null : $paymentMethod;

        // Create the transaction
        $transaction = $this->transactionService->createFromBookingFlow(
            $host,
            $client,
            $selectedItem,
            $isStripe ? Transaction::METHOD_STRIPE : Transaction::METHOD_MANUAL,
            $manualMethod
        );

        if ($isStripe) {
            // Create Stripe Checkout Session
            return $this->createStripeCheckoutSession($request, $host, $transaction, $bookingState);
        }

        // For manual payments, create booking/membership/etc but leave transaction as pending
        // The booking is created but payment confirmation is pending
        $booking = $this->createPendingBookingFromTransaction($transaction);

        // Create invoice for manual payment (as draft/pending)
        $this->createPendingInvoice($transaction);

        // Send confirmation email (with pending status)
        $this->transactionService->sendConfirmationEmail($transaction, $booking);

        // Clear booking state
        $this->bookingService->clearState($request);

        return redirect()->route('booking.confirmation', [
            'subdomain' => $host->subdomain,
            'transaction' => $transaction->transaction_id,
        ]);
    }

    /**
     * Process a membership-based booking (no payment required)
     */
    protected function processMembershipBooking(Request $request, Host $host, Client $client, array $selectedItem)
    {
        $membershipId = $selectedItem['membership_id'] ?? null;

        if (!$membershipId) {
            return back()->with('error', 'Invalid membership selection. Please try again.');
        }

        // Verify membership is still valid
        $membership = CustomerMembership::where('id', $membershipId)
            ->where('host_id', $host->id)
            ->where('client_id', $client->id)
            ->active()
            ->notExpired()
            ->withCredits()
            ->first();

        if (!$membership) {
            return back()->with('error', 'Your membership is no longer valid. Please select a different payment method.');
        }

        // Create the transaction (with $0 amount for membership booking)
        $transaction = $this->transactionService->createFromBookingFlow(
            $host,
            $client,
            $selectedItem,
            Transaction::METHOD_MEMBERSHIP,
            null
        );

        // Create the booking immediately (membership bookings are instantly confirmed)
        $booking = $this->transactionService->createMembershipBooking($transaction, $membership);

        if (!$booking) {
            return back()->with('error', 'Unable to complete booking. Please try again.');
        }

        // Mark transaction as paid (since no payment is needed)
        $transaction->markPaid();

        // Deduct credit from membership
        $membership->deductCredit();

        // Create invoice (for $0)
        $this->createPendingInvoice($transaction);

        // Send confirmation email
        $this->transactionService->sendConfirmationEmail($transaction, $booking);

        // Assign intake forms if applicable
        $this->transactionService->assignIntakeForms($transaction, $booking);

        // Clear booking state
        $this->bookingService->clearState($request);

        return redirect()->route('booking.confirmation', [
            'subdomain' => $host->subdomain,
            'transaction' => $transaction->transaction_id,
        ]);
    }

    /**
     * Create pending booking from transaction for manual payments
     * Creates bookings for classes/services, but memberships/packs are not activated until payment confirmed
     */
    protected function createPendingBookingFromTransaction(Transaction $transaction): ?\App\Models\Booking
    {
        // For class and service bookings, create the booking even for pending payment
        // This reserves their spot while awaiting payment
        if (in_array($transaction->type, [Transaction::TYPE_CLASS_BOOKING, Transaction::TYPE_SERVICE_BOOKING])) {
            return $this->transactionService->createBookingFromTransaction($transaction);
        }

        // For memberships and class packs, don't activate until payment is confirmed
        // The transaction record exists but no CustomerMembership or ClassPackPurchase is created yet
        return null;
    }

    /**
     * Create a pending invoice for manual payments
     */
    protected function createPendingInvoice(Transaction $transaction): void
    {
        $invoiceService = app(\App\Services\InvoiceService::class);
        $invoiceService->createFromTransaction($transaction);
    }

    /**
     * Create Stripe Checkout Session
     */
    protected function createStripeCheckoutSession(Request $request, Host $host, Transaction $transaction, array $bookingState)
    {
        if (empty($host->stripe_account_id)) {
            $transaction->markFailed('Stripe not configured for this studio');
            return back()->with('error', 'Online payments are not available at this time.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $selectedItem = $bookingState['selected_item'];
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => (int) ($transaction->total_amount * 100), // Stripe uses cents
                        'product_data' => [
                            'name' => $selectedItem['name'] ?? 'Booking',
                            'description' => $this->buildStripeDescription($selectedItem),
                        ],
                    ],
                    'quantity' => 1,
                ],
            ];

            $checkoutSession = StripeSession::create([
                'mode' => 'payment',
                'line_items' => $lineItems,
                'success_url' => route('booking.stripe-success', [
                    'subdomain' => $host->subdomain,
                    'transaction' => $transaction->transaction_id,
                ]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('booking.stripe-cancel', [
                    'subdomain' => $host->subdomain,
                    'transaction' => $transaction->transaction_id,
                ]),
                'customer_email' => $bookingState['contact_info']['email'] ?? null,
                'metadata' => [
                    'transaction_id' => $transaction->transaction_id,
                    'host_id' => $host->id,
                    'client_id' => $transaction->client_id,
                ],
            ], [
                'stripe_account' => $host->stripe_account_id,
            ]);

            // Save the checkout session ID
            $transaction->update([
                'stripe_checkout_session_id' => $checkoutSession->id,
            ]);

            return redirect($checkoutSession->url);

        } catch (\Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->transaction_id,
                'host_id' => $host->id,
            ]);

            $transaction->markFailed($e->getMessage());
            return back()->with('error', 'Unable to process payment. Please try again or choose a different payment method.');
        }
    }

    /**
     * Build description for Stripe line item
     */
    protected function buildStripeDescription(array $selectedItem): string
    {
        $parts = [];

        if (!empty($selectedItem['datetime'])) {
            $parts[] = $selectedItem['datetime'];
        }
        if (!empty($selectedItem['instructor'])) {
            $parts[] = 'with ' . $selectedItem['instructor'];
        }
        if (!empty($selectedItem['location'])) {
            $parts[] = 'at ' . $selectedItem['location'];
        }

        return implode(' • ', $parts) ?: 'Studio booking';
    }

    /**
     * Handle Stripe success callback
     */
    public function stripeSuccess(Request $request)
    {
        $host = $this->getHost($request);
        $transactionId = $request->route('transaction');

        $transaction = Transaction::where('transaction_id', $transactionId)
            ->where('host_id', $host->id)
            ->first();

        if (!$transaction) {
            return redirect()->route('booking.select-type', ['subdomain' => $host->subdomain])
                ->with('error', 'Transaction not found.');
        }

        // Verify the checkout session with Stripe
        $sessionId = $request->query('session_id');
        if ($sessionId && $transaction->stripe_checkout_session_id === $sessionId) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));

                $session = StripeSession::retrieve($sessionId, [
                    'stripe_account' => $host->stripe_account_id,
                ]);

                if ($session->payment_status === 'paid') {
                    // Update transaction and create booking/membership/etc
                    $transaction->update([
                        'stripe_payment_intent_id' => $session->payment_intent,
                    ]);

                    $this->transactionService->processSuccessfulPayment($transaction);
                }
            } catch (\Exception $e) {
                Log::error('Stripe session verification failed', [
                    'error' => $e->getMessage(),
                    'transaction_id' => $transactionId,
                    'session_id' => $sessionId,
                ]);
            }
        }

        // Clear booking state
        $this->bookingService->clearState($request);

        return redirect()->route('booking.confirmation', [
            'subdomain' => $host->subdomain,
            'transaction' => $transactionId,
        ]);
    }

    /**
     * Handle Stripe cancel callback
     */
    public function stripeCancel(Request $request)
    {
        $host = $this->getHost($request);
        $transactionId = $request->route('transaction');

        $transaction = Transaction::where('transaction_id', $transactionId)
            ->where('host_id', $host->id)
            ->first();

        if ($transaction && $transaction->status === Transaction::STATUS_PENDING) {
            $transaction->markCancelled('User cancelled at Stripe checkout');
        }

        return redirect()->route('booking.payment', ['subdomain' => $host->subdomain])
            ->with('error', 'Payment was cancelled. Please try again or choose a different payment method.');
    }

    /**
     * Booking confirmation page
     */
    public function confirmation(Request $request)
    {
        $host = $this->getHost($request);
        $transactionId = $request->route('transaction');

        $transaction = Transaction::where('transaction_id', $transactionId)
            ->where('host_id', $host->id)
            ->with(['client', 'booking', 'purchasable', 'invoice'])
            ->first();

        if (!$transaction) {
            return redirect()->route('booking.select-type', ['subdomain' => $host->subdomain])
                ->with('error', 'Transaction not found.');
        }

        // Get payment instructions for manual payments
        $paymentInstructions = null;
        if ($transaction->payment_method === Transaction::METHOD_MANUAL && $transaction->manual_method) {
            $paymentInstructions = $this->transactionService->getManualPaymentInstructions($host, $transaction->manual_method);
        }

        return view('subdomain.booking.confirmation', [
            'host' => $host,
            'transaction' => $transaction,
            'paymentInstructions' => $paymentInstructions,
        ]);
    }

    /**
     * Handle Stripe webhook
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
        }

        return response('OK', 200);
    }

    /**
     * Handle checkout.session.completed webhook event
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        $transactionId = $session->metadata->transaction_id ?? null;
        if (!$transactionId) {
            Log::warning('Stripe webhook: No transaction_id in metadata', ['session_id' => $session->id]);
            return;
        }

        $transaction = Transaction::where('transaction_id', $transactionId)->first();
        if (!$transaction) {
            Log::warning('Stripe webhook: Transaction not found', ['transaction_id' => $transactionId]);
            return;
        }

        if ($transaction->status === Transaction::STATUS_PAID) {
            return; // Already processed
        }

        if ($session->payment_status === 'paid') {
            $transaction->update([
                'stripe_payment_intent_id' => $session->payment_intent,
            ]);
            $this->transactionService->processSuccessfulPayment($transaction);

            Log::info('Stripe webhook: Payment processed successfully', [
                'transaction_id' => $transactionId,
                'payment_intent' => $session->payment_intent,
            ]);
        }
    }

    /**
     * Handle payment_intent.succeeded webhook event
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $transaction = Transaction::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if (!$transaction) {
            Log::info('Stripe webhook: No transaction found for payment intent', ['payment_intent' => $paymentIntent->id]);
            return;
        }

        if ($transaction->status !== Transaction::STATUS_PAID) {
            $this->transactionService->processSuccessfulPayment($transaction);

            Log::info('Stripe webhook: Payment marked as paid', [
                'transaction_id' => $transaction->transaction_id,
                'payment_intent' => $paymentIntent->id,
            ]);
        }
    }

    /**
     * Handle payment_intent.payment_failed webhook event
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $transaction = Transaction::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($transaction && $transaction->status === Transaction::STATUS_PENDING) {
            $failureMessage = $paymentIntent->last_payment_error->message ?? 'Payment failed';
            $transaction->markFailed($failureMessage);

            Log::info('Stripe webhook: Payment marked as failed', [
                'transaction_id' => $transaction->transaction_id,
                'reason' => $failureMessage,
            ]);
        }
    }
}
