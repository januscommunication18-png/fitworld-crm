<?php

namespace App\Services;

use App\Models\ClassPack;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\Host;
use App\Models\MembershipPlan;
use App\Models\ServiceSlot;
use Illuminate\Http\Request;

class BookingFlowService
{
    const SESSION_KEY = 'booking_flow';

    /**
     * Get the current booking state from session
     */
    public function getState(Request $request): array
    {
        return $request->session()->get(self::SESSION_KEY, [
            'booking_type' => null,
            'selected_item' => null,
            'contact_info' => null,
            'payment_method' => null,
            'terms_accepted' => false,
            'started_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Set booking type (class_session, service_slot, membership_plan, class_pack)
     */
    public function setBookingType(Request $request, string $type): void
    {
        $state = $this->getState($request);
        $state['booking_type'] = $type;
        $state['selected_item'] = null; // Reset item when type changes
        $request->session()->put(self::SESSION_KEY, $state);
    }

    /**
     * Set the selected item details
     */
    public function setSelectedItem(Request $request, array $item): void
    {
        $state = $this->getState($request);
        $state['selected_item'] = $item;
        $request->session()->put(self::SESSION_KEY, $state);
    }

    /**
     * Set contact information
     */
    public function setContactInfo(Request $request, array $contactInfo): void
    {
        $state = $this->getState($request);
        $state['contact_info'] = $contactInfo;
        $request->session()->put(self::SESSION_KEY, $state);
    }

    /**
     * Set payment method
     */
    public function setPaymentMethod(Request $request, string $method): void
    {
        $state = $this->getState($request);
        $state['payment_method'] = $method;
        $request->session()->put(self::SESSION_KEY, $state);
    }

    /**
     * Set terms accepted
     */
    public function setTermsAccepted(Request $request, bool $accepted): void
    {
        $state = $this->getState($request);
        $state['terms_accepted'] = $accepted;
        $request->session()->put(self::SESSION_KEY, $state);
    }

    /**
     * Clear the booking state
     */
    public function clearState(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Get the selected item model
     */
    public function getSelectedItemModel(Request $request): ?object
    {
        $state = $this->getState($request);
        $item = $state['selected_item'] ?? null;

        if (!$item) {
            return null;
        }

        return match ($item['type'] ?? null) {
            'class_session' => ClassSession::find($item['id']),
            'service_slot' => ServiceSlot::find($item['id']),
            'membership_plan' => MembershipPlan::find($item['id']),
            'class_pack' => ClassPack::find($item['id']),
            default => null,
        };
    }

    /**
     * Get or create client from contact info
     */
    public function getOrCreateClient(Request $request, Host $host): ?Client
    {
        $state = $this->getState($request);
        $contact = $state['contact_info'] ?? null;

        if (!$contact) {
            return null;
        }

        // Try to find existing client
        $client = Client::where('host_id', $host->id)
            ->where('email', strtolower($contact['email']))
            ->first();

        if (!$client) {
            // Create new client
            $client = Client::create([
                'host_id' => $host->id,
                'first_name' => $contact['first_name'],
                'last_name' => $contact['last_name'],
                'email' => strtolower($contact['email']),
                'phone' => $contact['phone'],
                'status' => Client::STATUS_LEAD,
                'lead_source' => Client::SOURCE_WEBSITE,
            ]);
        } else {
            // Update phone if different
            if ($client->phone !== $contact['phone']) {
                $client->update(['phone' => $contact['phone']]);
            }
        }

        return $client;
    }

    /**
     * Calculate total amount for the booking
     */
    public function calculateTotal(Request $request): float
    {
        $state = $this->getState($request);
        $item = $state['selected_item'] ?? null;

        if (!$item) {
            return 0;
        }

        return (float) ($item['price'] ?? 0);
    }

    /**
     * Check if the booking state is valid for proceeding to payment
     */
    public function isReadyForPayment(Request $request): bool
    {
        $state = $this->getState($request);

        return !empty($state['selected_item'])
            && !empty($state['contact_info'])
            && !empty($state['contact_info']['email']);
    }

    /**
     * Get a summary of the current booking for display
     */
    public function getSummary(Request $request): array
    {
        $state = $this->getState($request);
        $item = $state['selected_item'] ?? [];
        $contact = $state['contact_info'] ?? [];

        return [
            'item_name' => $item['name'] ?? 'Not selected',
            'item_type' => $this->formatBookingType($state['booking_type'] ?? ''),
            'item_datetime' => $item['datetime'] ?? null,
            'item_instructor' => $item['instructor'] ?? null,
            'item_location' => $item['location'] ?? null,
            'price' => $item['price'] ?? 0,
            'formatted_price' => '$' . number_format($item['price'] ?? 0, 2),
            'contact_name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
            'contact_email' => $contact['email'] ?? null,
            'contact_phone' => $contact['phone'] ?? null,
            'is_waitlist' => $item['is_waitlist'] ?? false,
        ];
    }

    /**
     * Format booking type for display
     */
    protected function formatBookingType(?string $type): string
    {
        return match ($type) {
            'class_session' => 'Class',
            'service_slot' => 'Service',
            'membership_plan' => 'Membership',
            'class_pack' => 'Class Pack',
            default => 'Booking',
        };
    }
}
