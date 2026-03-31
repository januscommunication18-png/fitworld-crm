@extends('backoffice.layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Expenses</h1>
            <p class="text-base-content/60 text-sm">Track and manage business expenses</p>
        </div>
        <a href="{{ route('backoffice.expenses.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Expense
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--receipt] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($stats['total'], 2) }}</p>
                        <p class="text-xs text-base-content/50">Total Expenses</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--calendar-month] size-5 text-info"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($stats['this_month'], 2) }}</p>
                        <p class="text-xs text-base-content/50">This Month</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--repeat] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($stats['recurring'], 2) }}</p>
                        <p class="text-xs text-base-content/50">Recurring</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--receipt-off] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">${{ number_format($stats['one_time'], 2) }}</p>
                        <p class="text-xs text-base-content/50">One-Time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <form action="{{ route('backoffice.expenses.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
                <div class="join flex-1 max-w-md">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input join-item flex-1" placeholder="Search expenses...">
                    <button type="submit" class="btn btn-primary join-item">
                        <span class="icon-[tabler--search] size-5"></span>
                    </button>
                </div>
                <select name="category" class="select select-bordered" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ ($filters['category'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="recurring" class="select select-bordered" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="yes" {{ ($filters['recurring'] ?? '') === 'yes' ? 'selected' : '' }}>Recurring</option>
                    <option value="no" {{ ($filters['recurring'] ?? '') === 'no' ? 'selected' : '' }}>One-Time</option>
                </select>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="input input-bordered" placeholder="Start Date">
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="input input-bordered" placeholder="End Date">
                @if(array_filter($filters))
                    <a href="{{ route('backoffice.expenses.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Expenses Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            @if($expenses->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--receipt] size-8 text-base-content/30"></span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">No Expenses Found</h3>
                    <p class="text-base-content/60 mb-4">Start tracking your expenses.</p>
                    <a href="{{ route('backoffice.expenses.create') }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add First Expense
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Expense</th>
                                <th>Category</th>
                                <th>Service</th>
                                <th class="text-right">Amount</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $expense)
                            <tr class="hover">
                                <td>
                                    <div>
                                        <p class="font-medium">{{ $expense->name }}</p>
                                        @if($expense->invoice_path)
                                            <span class="badge badge-ghost badge-xs">
                                                <span class="icon-[tabler--file] size-3 mr-1"></span>
                                                Invoice
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft badge-primary badge-sm">{{ $expense->category_label }}</span>
                                </td>
                                <td class="text-sm text-base-content/70">{{ $expense->service_name ?? '-' }}</td>
                                <td class="text-right font-mono font-semibold">
                                    ${{ number_format($expense->amount, 2) }}
                                    <span class="text-xs text-base-content/50">{{ $expense->currency }}</span>
                                </td>
                                <td>
                                    @if($expense->is_recurring)
                                        <span class="badge badge-warning badge-soft badge-sm">
                                            <span class="icon-[tabler--repeat] size-3 mr-1"></span>
                                            Recurring
                                        </span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">One-Time</span>
                                    @endif
                                </td>
                                <td class="text-sm">{{ $expense->service_date->format('M d, Y') }}</td>
                                <td>
                                    <div class="flex items-center gap-1 justify-end">
                                        @if($expense->invoice_path)
                                            <a href="{{ route('backoffice.expenses.download-invoice', $expense) }}" class="btn btn-ghost btn-sm btn-circle" title="Download Invoice">
                                                <span class="icon-[tabler--download] size-4"></span>
                                            </a>
                                        @endif
                                        <a href="{{ route('backoffice.expenses.edit', $expense) }}" class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <form action="{{ route('backoffice.expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($expenses->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $expenses->withQueryString()->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
