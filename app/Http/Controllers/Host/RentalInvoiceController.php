<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\MembershipPlan;
use App\Models\RentalBooking;
use App\Models\RentalItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RentalInvoiceController extends Controller
{
    public function create()
    {
        $host = auth()->user()->host;
        $clients = $host->clients()->orderBy('first_name')->orderBy('last_name')->get();
        $rentalItems = $host->rentalItems()->where('is_active', true)->orderBy('name')->get();
        $manualMethods = Transaction::getManualMethods();

        // Multi-currency support
        $hostCurrencies = $host->currencies ?? ['USD'];
        $defaultCurrency = $host->default_currency ?? 'USD';
        $currencySymbols = MembershipPlan::getCurrencySymbols();

        return view('host.rentals.invoice.create', compact(
            'clients',
            'rentalItems',
            'manualMethods',
            'hostCurrencies',
            'defaultCurrency',
            'currencySymbols'
        ));
    }

    public function store(Request $request)
    {
        $host = auth()->user()->host;

        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.rental_item_id' => 'required|exists:rental_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'payment_method' => 'required|string',
            'manual_method' => 'required_if:payment_method,manual|nullable|string',
            'rental_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:rental_date',
            'notes' => 'nullable|string|max:1000',
            'collect_deposit' => 'nullable|boolean',
        ]);

        $currency = $request->input('currency');
        $subtotal = 0;
        $depositTotal = 0;
        $rentalBookings = [];

        // Calculate totals and prepare rental bookings
        foreach ($request->input('items') as $item) {
            $rentalItem = RentalItem::find($item['rental_item_id']);

            if (!$rentalItem || $rentalItem->host_id !== $host->id) {
                continue;
            }

            $quantity = (int) $item['quantity'];
            $unitPrice = $rentalItem->getPriceForCurrency($currency) ?? 0;
            $depositAmount = $rentalItem->getDepositForCurrency($currency) ?? 0;
            $itemTotal = $unitPrice * $quantity;
            $itemDeposit = $depositAmount * $quantity;

            $subtotal += $itemTotal;
            $depositTotal += $itemDeposit;

            $rentalBookings[] = [
                'rental_item' => $rentalItem,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal,
                'deposit_amount' => $request->boolean('collect_deposit') ? $itemDeposit : 0,
            ];
        }

        // Include deposit in total if collecting
        $totalAmount = $subtotal;
        if ($request->boolean('collect_deposit')) {
            $totalAmount += $depositTotal;
        }

        // Create the transaction
        $transaction = Transaction::create([
            'host_id' => $host->id,
            'client_id' => $request->input('client_id'),
            'type' => Transaction::TYPE_RENTAL,
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'status' => Transaction::STATUS_PAID,
            'payment_method' => $request->input('payment_method'),
            'manual_method' => $request->input('payment_method') === 'manual' ? $request->input('manual_method') : null,
            'paid_at' => now(),
            'notes' => $request->input('notes'),
            'metadata' => [
                'deposit_collected' => $request->boolean('collect_deposit'),
                'deposit_amount' => $depositTotal,
            ],
        ]);

        // Create rental bookings
        $rentalDate = Carbon::parse($request->input('rental_date'));
        $dueDate = $request->input('due_date') ? Carbon::parse($request->input('due_date')) : null;

        foreach ($rentalBookings as $bookingData) {
            $rentalItem = $bookingData['rental_item'];

            $booking = RentalBooking::create([
                'host_id' => $host->id,
                'rental_item_id' => $rentalItem->id,
                'client_id' => $request->input('client_id'),
                'transaction_id' => $transaction->id,
                'quantity' => $bookingData['quantity'],
                'unit_price' => $bookingData['unit_price'],
                'total_price' => $bookingData['total_price'],
                'deposit_amount' => $bookingData['deposit_amount'],
                'currency' => $currency,
                'rental_date' => $rentalDate,
                'due_date' => $dueDate,
                'fulfillment_status' => 'pending',
            ]);

            // Decrement available inventory
            $rentalItem->decrement('available_inventory', $bookingData['quantity']);

            // Log the inventory change
            $rentalItem->inventoryLogs()->create([
                'rental_booking_id' => $booking->id,
                'action' => 'booked',
                'quantity_change' => -$bookingData['quantity'],
                'inventory_after' => $rentalItem->available_inventory,
                'notes' => 'Rental invoice #' . $transaction->transaction_id,
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('rentals.fulfillment.index')
            ->with('success', 'Rental invoice created successfully. Transaction ID: ' . $transaction->transaction_id);
    }

    /**
     * Get rental item details via AJAX
     */
    public function getItemPrice(Request $request, RentalItem $rentalItem)
    {
        $host = auth()->user()->host;

        if ($rentalItem->host_id !== $host->id) {
            abort(403);
        }

        $currency = $request->input('currency', $host->default_currency ?? 'USD');

        return response()->json([
            'price' => $rentalItem->getPriceForCurrency($currency) ?? 0,
            'deposit' => $rentalItem->getDepositForCurrency($currency) ?? 0,
            'available' => $rentalItem->available_inventory,
        ]);
    }
}
