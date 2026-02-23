<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\Segment;
use App\Models\ClassPlan;
use App\Models\ServicePlan;
use App\Models\MembershipPlan;
use App\Models\ClassPack;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $host = auth()->user()->host;
        $status = $request->get('status');

        $offers = Offer::forHost($host->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with('segment')
            ->orderByDesc('created_at')
            ->paginate(20);

        $statuses = Offer::getStatuses();

        // Summary stats
        $stats = [
            'total_offers' => Offer::forHost($host->id)->count(),
            'active_offers' => Offer::forHost($host->id)->active()->count(),
            'total_redemptions' => Offer::forHost($host->id)->sum('total_redemptions'),
            'total_discount_given' => Offer::forHost($host->id)->sum('total_discount_given'),
            'total_revenue' => Offer::forHost($host->id)->sum('total_revenue_generated'),
        ];

        return view('host.offers.index', compact('offers', 'status', 'statuses', 'stats'));
    }

    public function create()
    {
        $host = auth()->user()->host;

        $statuses = Offer::getStatuses();
        $appliesToOptions = Offer::getAppliesTo();
        $discountTypes = Offer::getDiscountTypes();
        $targetAudiences = Offer::getTargetAudiences();

        // For item selection
        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $servicePlans = $host->servicePlans()->active()->orderBy('name')->get();
        $membershipPlans = $host->membershipPlans()->active()->orderBy('name')->get();
        $classPacks = $host->classPacks()->where('status', 'active')->orderBy('name')->get();

        // Segments for targeting
        $segments = Segment::forHost($host->id)->active()->orderBy('name')->get();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';

        return view('host.offers.create', compact(
            'statuses',
            'appliesToOptions',
            'discountTypes',
            'targetAudiences',
            'classPlans',
            'servicePlans',
            'membershipPlans',
            'classPacks',
            'segments',
            'hostCurrencies',
            'defaultCurrency'
        ));
    }

    public function store(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:offers,code,NULL,id,host_id,' . $host->id,
            'description' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,active,paused',

            // Duration
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'auto_expire' => 'boolean',

            // Applicability
            'applies_to' => 'required|in:all,classes,services,memberships,retail,class_packs,specific',
            'applicable_item_ids' => 'nullable|array',
            'plan_scope' => 'required|in:all_plans,specific_plans,first_time,trial,upgrade',
            'applicable_plan_ids' => 'nullable|array',

            // Discount
            'discount_type' => 'required|in:percentage,fixed_amount,buy_x_get_y,free_class,free_addon,bundle',
            'discount_value' => 'nullable|numeric|min:0',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'free_classes' => 'nullable|integer|min:1',
            'discount_amounts' => 'nullable|array',

            // Target audience
            'target_audience' => 'required|in:all_members,specific_segment,new_members,inactive_members,high_spenders,vip_tier',
            'segment_id' => 'nullable|exists:segments,id',

            // Usage control
            'total_usage_limit' => 'nullable|integer|min:1',
            'per_member_limit' => 'nullable|integer|min:1',
            'first_x_users' => 'nullable|integer|min:1',
            'auto_stop_on_limit' => 'boolean',

            // Channel control
            'online_booking_only' => 'boolean',
            'front_desk_only' => 'boolean',
            'app_only' => 'boolean',
            'manual_override_allowed' => 'boolean',

            // Stack rules
            'can_combine' => 'boolean',

            // Auto apply
            'auto_apply' => 'boolean',
            'require_code' => 'boolean',

            // Invoice
            'show_on_invoice' => 'boolean',
            'invoice_line_text' => 'nullable|string|max:255',
        ]);

        // Generate code if required but not provided
        if ($request->boolean('require_code') && empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::random(8));
        }

        // Ensure code is unique
        if (!empty($validated['code'])) {
            $counter = 1;
            $baseCode = $validated['code'];
            while (Offer::forHost($host->id)->where('code', $validated['code'])->exists()) {
                $validated['code'] = $baseCode . $counter++;
            }
        }

        $offer = Offer::create([
            'host_id' => $host->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => $validated['status'],

            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'auto_expire' => $request->boolean('auto_expire', true),

            'applies_to' => $validated['applies_to'],
            'applicable_item_ids' => $validated['applicable_item_ids'] ?? null,
            'plan_scope' => $validated['plan_scope'],
            'applicable_plan_ids' => $validated['applicable_plan_ids'] ?? null,

            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? null,
            'buy_quantity' => $validated['buy_quantity'] ?? null,
            'get_quantity' => $validated['get_quantity'] ?? null,
            'free_classes' => $validated['free_classes'] ?? null,
            'discount_amounts' => $validated['discount_amounts'] ?? null,

            'target_audience' => $validated['target_audience'],
            'segment_id' => $validated['segment_id'] ?? null,

            'total_usage_limit' => $validated['total_usage_limit'] ?? null,
            'per_member_limit' => $validated['per_member_limit'] ?? null,
            'first_x_users' => $validated['first_x_users'] ?? null,
            'auto_stop_on_limit' => $request->boolean('auto_stop_on_limit', true),

            'online_booking_only' => $request->boolean('online_booking_only'),
            'front_desk_only' => $request->boolean('front_desk_only'),
            'app_only' => $request->boolean('app_only'),
            'manual_override_allowed' => $request->boolean('manual_override_allowed', true),

            'can_combine' => $request->boolean('can_combine'),
            'highest_discount_wins' => true,

            'auto_apply' => $request->boolean('auto_apply'),
            'require_code' => $request->boolean('require_code'),

            'show_on_invoice' => $request->boolean('show_on_invoice', true),
            'invoice_line_text' => $validated['invoice_line_text'] ?? null,

            'created_by' => auth()->id(),
        ]);

        return redirect()->route('offers.show', $offer)
            ->with('success', 'Offer created successfully.');
    }

    public function show(Offer $offer)
    {
        $this->authorizeHost($offer);

        $offer->load('segment', 'createdBy');

        // Get recent redemptions
        $recentRedemptions = $offer->redemptions()
            ->with('client')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Analytics
        $analytics = [
            'total_redemptions' => $offer->total_redemptions,
            'total_discount_given' => $offer->total_discount_given,
            'total_revenue' => $offer->total_revenue_generated,
            'new_members' => $offer->new_members_acquired,
            'avg_discount' => $offer->total_redemptions > 0
                ? $offer->total_discount_given / $offer->total_redemptions
                : 0,
        ];

        // Redemptions by channel
        $byChannel = OfferRedemption::where('offer_id', $offer->id)
            ->notVoided()
            ->selectRaw('channel, COUNT(*) as count, SUM(discount_amount) as total_discount')
            ->groupBy('channel')
            ->get()
            ->keyBy('channel');

        return view('host.offers.show', compact('offer', 'recentRedemptions', 'analytics', 'byChannel'));
    }

    public function edit(Offer $offer)
    {
        $this->authorizeHost($offer);

        $host = auth()->user()->host;

        $statuses = Offer::getStatuses();
        $appliesToOptions = Offer::getAppliesTo();
        $discountTypes = Offer::getDiscountTypes();
        $targetAudiences = Offer::getTargetAudiences();

        $classPlans = $host->classPlans()->active()->orderBy('name')->get();
        $servicePlans = $host->servicePlans()->active()->orderBy('name')->get();
        $membershipPlans = $host->membershipPlans()->active()->orderBy('name')->get();
        $classPacks = $host->classPacks()->where('status', 'active')->orderBy('name')->get();

        $segments = Segment::forHost($host->id)->active()->orderBy('name')->get();

        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';

        return view('host.offers.edit', compact(
            'offer',
            'statuses',
            'appliesToOptions',
            'discountTypes',
            'targetAudiences',
            'classPlans',
            'servicePlans',
            'membershipPlans',
            'classPacks',
            'segments',
            'hostCurrencies',
            'defaultCurrency'
        ));
    }

    public function update(Request $request, Offer $offer)
    {
        $this->authorizeHost($offer);

        $host = auth()->user()->host;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:offers,code,' . $offer->id . ',id,host_id,' . $host->id,
            'description' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,active,paused,expired,archived',

            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'auto_expire' => 'boolean',

            'applies_to' => 'required|in:all,classes,services,memberships,retail,class_packs,specific',
            'applicable_item_ids' => 'nullable|array',
            'plan_scope' => 'required|in:all_plans,specific_plans,first_time,trial,upgrade',
            'applicable_plan_ids' => 'nullable|array',

            'discount_type' => 'required|in:percentage,fixed_amount,buy_x_get_y,free_class,free_addon,bundle',
            'discount_value' => 'nullable|numeric|min:0',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'free_classes' => 'nullable|integer|min:1',
            'discount_amounts' => 'nullable|array',

            'target_audience' => 'required|in:all_members,specific_segment,new_members,inactive_members,high_spenders,vip_tier',
            'segment_id' => 'nullable|exists:segments,id',

            'total_usage_limit' => 'nullable|integer|min:1',
            'per_member_limit' => 'nullable|integer|min:1',
            'first_x_users' => 'nullable|integer|min:1',
            'auto_stop_on_limit' => 'boolean',

            'online_booking_only' => 'boolean',
            'front_desk_only' => 'boolean',
            'app_only' => 'boolean',
            'manual_override_allowed' => 'boolean',

            'can_combine' => 'boolean',

            'auto_apply' => 'boolean',
            'require_code' => 'boolean',

            'show_on_invoice' => 'boolean',
            'invoice_line_text' => 'nullable|string|max:255',
        ]);

        $offer->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => $validated['status'],

            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'auto_expire' => $request->boolean('auto_expire', true),

            'applies_to' => $validated['applies_to'],
            'applicable_item_ids' => $validated['applicable_item_ids'] ?? null,
            'plan_scope' => $validated['plan_scope'],
            'applicable_plan_ids' => $validated['applicable_plan_ids'] ?? null,

            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? null,
            'buy_quantity' => $validated['buy_quantity'] ?? null,
            'get_quantity' => $validated['get_quantity'] ?? null,
            'free_classes' => $validated['free_classes'] ?? null,
            'discount_amounts' => $validated['discount_amounts'] ?? null,

            'target_audience' => $validated['target_audience'],
            'segment_id' => $validated['segment_id'] ?? null,

            'total_usage_limit' => $validated['total_usage_limit'] ?? null,
            'per_member_limit' => $validated['per_member_limit'] ?? null,
            'first_x_users' => $validated['first_x_users'] ?? null,
            'auto_stop_on_limit' => $request->boolean('auto_stop_on_limit', true),

            'online_booking_only' => $request->boolean('online_booking_only'),
            'front_desk_only' => $request->boolean('front_desk_only'),
            'app_only' => $request->boolean('app_only'),
            'manual_override_allowed' => $request->boolean('manual_override_allowed', true),

            'can_combine' => $request->boolean('can_combine'),

            'auto_apply' => $request->boolean('auto_apply'),
            'require_code' => $request->boolean('require_code'),

            'show_on_invoice' => $request->boolean('show_on_invoice', true),
            'invoice_line_text' => $validated['invoice_line_text'] ?? null,
        ]);

        return redirect()->route('offers.show', $offer)
            ->with('success', 'Offer updated successfully.');
    }

    public function destroy(Offer $offer)
    {
        $this->authorizeHost($offer);

        // Check if offer has redemptions
        if ($offer->redemptions()->exists()) {
            // Soft delete / archive instead
            $offer->update(['status' => Offer::STATUS_ARCHIVED]);
            return redirect()->route('offers.index')
                ->with('success', 'Offer has been archived (has redemption history).');
        }

        $offer->delete();

        return redirect()->route('offers.index')
            ->with('success', 'Offer deleted successfully.');
    }

    /**
     * Duplicate an offer
     */
    public function duplicate(Offer $offer)
    {
        $this->authorizeHost($offer);

        $newOffer = $offer->replicate();
        $newOffer->name = $offer->name . ' (Copy)';
        $newOffer->code = null; // Reset code
        $newOffer->status = Offer::STATUS_DRAFT;
        $newOffer->total_redemptions = 0;
        $newOffer->total_discount_given = 0;
        $newOffer->total_revenue_generated = 0;
        $newOffer->new_members_acquired = 0;
        $newOffer->conversion_rate = 0;
        $newOffer->created_by = auth()->id();
        $newOffer->save();

        return redirect()->route('offers.edit', $newOffer)
            ->with('success', 'Offer duplicated. You can now edit the copy.');
    }

    /**
     * Toggle offer status (quick pause/activate)
     */
    public function toggleStatus(Offer $offer)
    {
        $this->authorizeHost($offer);

        if ($offer->status === Offer::STATUS_ACTIVE) {
            $offer->update(['status' => Offer::STATUS_PAUSED]);
            $message = 'Offer paused.';
        } elseif (in_array($offer->status, [Offer::STATUS_DRAFT, Offer::STATUS_PAUSED])) {
            $offer->update(['status' => Offer::STATUS_ACTIVE]);
            $message = 'Offer activated.';
        } else {
            return back()->with('error', 'Cannot change status of expired or archived offers.');
        }

        return back()->with('success', $message);
    }

    /**
     * Validate a promo code
     */
    public function validateCode(Request $request)
    {
        $host = auth()->user()->host;

        $code = $request->input('code');
        $clientId = $request->input('client_id');

        $offer = Offer::forHost($host->id)
            ->available()
            ->withCode()
            ->where('code', strtoupper($code))
            ->first();

        if (!$offer) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired promo code.',
            ]);
        }

        // Check client eligibility if provided
        if ($clientId) {
            $client = \App\Models\Client::find($clientId);
            if ($client && !$offer->isClientEligible($client)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This offer is not available for this client.',
                ]);
            }
        }

        return response()->json([
            'valid' => true,
            'offer' => [
                'id' => $offer->id,
                'name' => $offer->name,
                'discount_type' => $offer->discount_type,
                'discount_value' => $offer->discount_value,
                'formatted_discount' => $offer->getFormattedDiscount(),
            ],
        ]);
    }

    protected function authorizeHost(Offer $offer): void
    {
        if ($offer->host_id !== auth()->user()->host_id) {
            abort(403, 'Unauthorized');
        }
    }
}
