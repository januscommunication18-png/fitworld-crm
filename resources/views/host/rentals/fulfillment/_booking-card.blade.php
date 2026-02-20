<div class="flex items-center gap-4 p-4 rounded-lg border {{ $isOverdue ?? false ? 'border-error bg-error/5' : 'border-base-200 bg-base-50' }}">
    {{-- Item Image --}}
    @if($booking->rentalItem?->primary_image)
        <img src="{{ Storage::url($booking->rentalItem->primary_image) }}" alt="" class="w-14 h-14 object-cover rounded-lg flex-shrink-0">
    @else
        <div class="w-14 h-14 rounded-lg bg-base-200 flex items-center justify-center flex-shrink-0">
            <span class="icon-[tabler--{{ $booking->rentalItem?->category_icon ?? 'package' }}] size-6 text-base-content/40"></span>
        </div>
    @endif

    {{-- Item & Customer Info --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <span class="font-semibold">{{ $booking->rentalItem?->name ?? 'Unknown Item' }}</span>
            <span class="badge badge-ghost badge-sm">x{{ $booking->quantity }}</span>
        </div>
        <div class="text-sm text-base-content/60">
            <span class="icon-[tabler--user] size-3 inline-block mr-1"></span>
            {{ $booking->client?->full_name ?? 'Walk-in' }}
        </div>
        @if($booking->bookable)
            <div class="text-xs text-base-content/50">
                @if($booking->bookable_type === 'App\\Models\\ClassBooking')
                    <span class="icon-[tabler--calendar] size-3 inline-block mr-1"></span>
                    {{ $booking->bookable->classSession?->classPlan?->name ?? 'Class Booking' }}
                @else
                    <span class="icon-[tabler--sparkles] size-3 inline-block mr-1"></span>
                    Service Booking
                @endif
            </div>
        @endif
        @if($isOverdue ?? false)
            <div class="text-xs text-error font-medium mt-1">
                <span class="icon-[tabler--alert-triangle] size-3 inline-block mr-1"></span>
                Due: {{ $booking->due_date?->format('M j, Y') ?? 'N/A' }} ({{ $booking->due_date?->diffForHumans() }})
            </div>
        @endif
    </div>

    {{-- Price & Actions --}}
    <div class="text-right flex-shrink-0">
        <div class="font-semibold">{{ $booking->formatted_total }}</div>
        @if($booking->deposit_amount > 0)
            <div class="text-xs text-base-content/60">Deposit: {{ $booking->formatted_deposit }}</div>
        @endif

        <div class="flex items-center gap-2 mt-2">
            @if($showPrepare ?? false)
                <form action="{{ route('rentals.fulfillment.prepare', $booking) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info btn-sm">
                        <span class="icon-[tabler--package] size-4"></span>
                        Prepare
                    </button>
                </form>
            @endif

            @if($showHandOut ?? false)
                <form action="{{ route('rentals.fulfillment.hand-out', $booking) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--hand-grab] size-4"></span>
                        Hand Out
                    </button>
                </form>
            @endif

            @if($showReturn ?? false)
                <button type="button" class="btn btn-success btn-sm" onclick="openReturnModal({{ $booking->id }})">
                    <span class="icon-[tabler--check] size-4"></span>
                    Return
                </button>
                <button type="button" class="btn btn-ghost btn-sm text-error" onclick="openLostModal({{ $booking->id }})">
                    Lost
                </button>
            @endif
        </div>
    </div>
</div>
