@extends('layouts.dashboard')

@section('title', 'Sell Class Pass')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index') }}"><span class="icon-[tabler--layout-grid] size-4"></span> Classes & Services</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'class-passes']) }}">Class Passes</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('class-passes.show', $classPass) }}">{{ $classPass->name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Sell</li>
    </ol>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('class-passes.show', $classPass) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Sell Class Pass</h1>
            <p class="text-base-content/60 mt-1">Assign "{{ $classPass->name }}" to a client.</p>
        </div>
    </div>

    {{-- Pass Summary Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: {{ $classPass->color ?? '#6366f1' }}20;">
                    <span class="icon-[tabler--ticket] size-8" style="color: {{ $classPass->color ?? '#6366f1' }};"></span>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-lg">{{ $classPass->name }}</h3>
                    <div class="flex items-center gap-4 text-sm text-base-content/60 mt-1">
                        <span>{{ $classPass->class_count }} credits</span>
                        <span>&bull;</span>
                        <span>{{ $classPass->formatted_validity }}</span>
                        <span>&bull;</span>
                        <span class="font-medium text-success">{{ $classPass->getFormattedPriceForCurrency($defaultCurrency) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sell Form --}}
    <form action="{{ route('class-passes.sell', $classPass) }}" method="POST">
        @csrf
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Sale Details</h3>
            </div>
            <div class="card-body space-y-4">
                {{-- Client Selection --}}
                <div>
                    <label class="label-text" for="client_id">Select Client <span class="text-error">*</span></label>
                    <select id="client_id" name="client_id" class="select w-full @error('client_id') select-error @enderror" required>
                        <option value="">Choose a client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} ({{ $client->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-base-content/60 mt-1">
                        <a href="{{ route('clients.create') }}" class="link link-primary" target="_blank">Create new client</a> if they don't exist yet.
                    </p>
                </div>

                {{-- Payment Method --}}
                <div>
                    <label class="label-text" for="payment_method">Payment Method <span class="text-error">*</span></label>
                    <select id="payment_method" name="payment_method" class="select w-full @error('payment_method') select-error @enderror" required>
                        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                        <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                        <option value="comp" {{ old('payment_method') === 'comp' ? 'selected' : '' }}>Complimentary (Free)</option>
                    </select>
                    @error('payment_method')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Amount Paid --}}
                <div id="amount-section">
                    <label class="label-text" for="amount_paid">Amount Paid</label>
                    <label class="input input-bordered flex items-center gap-1">
                        <span class="text-base-content/60">{{ $currencySymbols[$defaultCurrency] ?? '$' }}</span>
                        <input type="number" id="amount_paid" name="amount_paid" step="0.01" min="0"
                            value="{{ old('amount_paid', $classPass->getPriceForCurrency($defaultCurrency)) }}"
                            class="grow @error('amount_paid') input-error @enderror"
                            placeholder="0.00">
                    </label>
                    @error('amount_paid')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="label-text" for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="2"
                        class="textarea w-full @error('notes') textarea-error @enderror"
                        placeholder="Any notes about this sale...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <div class="flex items-center justify-between w-full">
                    <a href="{{ route('class-passes.show', $classPass) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-5"></span>
                        Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('payment_method');
    const amountSection = document.getElementById('amount-section');
    const amountInput = document.getElementById('amount_paid');

    paymentMethod.addEventListener('change', function() {
        if (this.value === 'comp') {
            amountSection.classList.add('hidden');
            amountInput.value = '0';
        } else {
            amountSection.classList.remove('hidden');
            amountInput.value = '{{ $classPass->getPriceForCurrency($defaultCurrency) }}';
        }
    });
});
</script>
@endpush
@endsection
