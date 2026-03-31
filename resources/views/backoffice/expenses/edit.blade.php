@extends('backoffice.layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('backoffice.expenses.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-xl font-semibold">Edit Expense</h1>
            <p class="text-base-content/60 text-sm">Update expense information</p>
        </div>
    </div>

    {{-- Form --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('backoffice.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="label-text font-medium" for="name">Name of Expense <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $expense->name) }}" class="input input-bordered w-full mt-1 @error('name') input-error @enderror" placeholder="e.g. AWS Monthly Bill" required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="label-text font-medium" for="category">Category <span class="text-error">*</span></label>
                    <select id="category" name="category" class="select select-bordered w-full mt-1 @error('category') select-error @enderror" required>
                        <option value="">Select a category...</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $expense->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Service Name --}}
                <div>
                    <label class="label-text font-medium" for="service_name">Service Name</label>
                    <input type="text" id="service_name" name="service_name" value="{{ old('service_name', $expense->service_name) }}" class="input input-bordered w-full mt-1 @error('service_name') input-error @enderror" placeholder="e.g. Amazon Web Services">
                    @error('service_name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Amount & Currency --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label-text font-medium" for="amount">Amount <span class="text-error">*</span></label>
                        <div class="join w-full mt-1">
                            <span class="btn btn-soft join-item pointer-events-none">$</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" class="input input-bordered join-item flex-1 @error('amount') input-error @enderror" placeholder="0.00" step="0.01" min="0.01" required>
                        </div>
                        @error('amount')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text font-medium" for="currency">Currency <span class="text-error">*</span></label>
                        <select id="currency" name="currency" class="select select-bordered w-full mt-1" required>
                            <option value="USD" {{ old('currency', $expense->currency) === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            <option value="EUR" {{ old('currency', $expense->currency) === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="GBP" {{ old('currency', $expense->currency) === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                            <option value="CAD" {{ old('currency', $expense->currency) === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency', $expense->currency) === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                            <option value="INR" {{ old('currency', $expense->currency) === 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                        </select>
                    </div>
                </div>

                {{-- Is Recurring --}}
                <div>
                    <label class="label-text font-medium mb-2 block">Is this recurring?</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="is_recurring" value="1" class="radio radio-primary" {{ old('is_recurring', $expense->is_recurring) ? 'checked' : '' }}>
                            <span class="icon-[tabler--repeat] size-4"></span>
                            <span>Yes, Recurring</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="is_recurring" value="0" class="radio radio-primary" {{ !old('is_recurring', $expense->is_recurring) ? 'checked' : '' }}>
                            <span class="icon-[tabler--receipt-off] size-4"></span>
                            <span>No, One-Time</span>
                        </label>
                    </div>
                </div>

                {{-- Service Date --}}
                <div>
                    <label class="label-text font-medium" for="service_date">Date of Service <span class="text-error">*</span></label>
                    <input type="date" id="service_date" name="service_date" value="{{ old('service_date', $expense->service_date->format('Y-m-d')) }}" class="input input-bordered w-full mt-1 @error('service_date') input-error @enderror" required>
                    @error('service_date')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Current Invoice --}}
                @if($expense->invoice_path)
                <div class="p-3 bg-base-200/50 rounded-lg">
                    <label class="label-text font-medium mb-2 block">Current Invoice</label>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--file] size-5 text-base-content/50"></span>
                            <span class="text-sm">{{ basename($expense->invoice_path) }}</span>
                        </div>
                        <a href="{{ route('backoffice.expenses.download-invoice', $expense) }}" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--download] size-4"></span>
                            Download
                        </a>
                    </div>
                </div>
                @endif

                {{-- Invoice Upload --}}
                <div>
                    <label class="label-text font-medium" for="invoice">{{ $expense->invoice_path ? 'Replace Invoice' : 'Upload Invoice' }}</label>
                    <div class="mt-1">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-base-content/20 rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <span class="icon-[tabler--cloud-upload] size-8 text-base-content/40 mb-2"></span>
                                <p class="text-sm text-base-content/60"><span class="font-medium">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-base-content/40">PDF, JPG, PNG (max 10MB)</p>
                            </div>
                            <input type="file" id="invoice" name="invoice" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                        </label>
                        <p id="file-name" class="text-sm text-base-content/60 mt-2 hidden"></p>
                    </div>
                    @error('invoice')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="label-text font-medium" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="textarea textarea-bordered w-full mt-1" rows="3" placeholder="Optional notes about this expense...">{{ old('notes', $expense->notes) }}</textarea>
                    @error('notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex justify-between pt-4 border-t border-base-200">
                    <form action="{{ route('backoffice.expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error btn-outline">
                            <span class="icon-[tabler--trash] size-4"></span>
                            Delete
                        </button>
                    </form>
                    <div class="flex gap-3">
                        <a href="{{ route('backoffice.expenses.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-4"></span>
                            Update Expense
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Show selected file name
    document.getElementById('invoice').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const fileNameEl = document.getElementById('file-name');
        if (fileName) {
            fileNameEl.textContent = 'Selected: ' + fileName;
            fileNameEl.classList.remove('hidden');
        } else {
            fileNameEl.classList.add('hidden');
        }
    });
</script>
@endsection
