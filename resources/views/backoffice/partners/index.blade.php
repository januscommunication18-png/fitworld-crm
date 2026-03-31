@extends('backoffice.layouts.app')

@section('title', 'Partners')
@section('page-title', 'Partners')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Partners</h1>
            <p class="text-base-content/60 text-sm">Manage partner profit sharing percentages</p>
        </div>
        <a href="{{ route('backoffice.partners.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Partner
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Percentage Summary Card --}}
    <div class="card bg-base-100">
        <div class="card-body py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--percentage] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/60">Total Allocated</p>
                        <p class="text-2xl font-bold {{ $totalPercentage >= 100 ? 'text-success' : 'text-warning' }}">{{ number_format($totalPercentage, 2) }}%</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-base-content/60">Remaining</p>
                    <p class="text-2xl font-bold {{ $remainingPercentage > 0 ? 'text-info' : 'text-success' }}">{{ number_format($remainingPercentage, 2) }}%</p>
                </div>
            </div>
            {{-- Progress bar --}}
            <div class="mt-4">
                <div class="w-full bg-base-200 rounded-full h-3">
                    <div class="bg-primary h-3 rounded-full transition-all" style="width: {{ min($totalPercentage, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Partners Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            @if($partners->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--users] size-8 text-base-content/30"></span>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">No Partners Yet</h3>
                    <p class="text-base-content/60 mb-4">Add partners to manage profit sharing.</p>
                    <a href="{{ route('backoffice.partners.create') }}" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add First Partner
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th>Email</th>
                                <th class="text-right">Percentage</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partners as $partner)
                            <tr class="hover">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary">
                                                <span class="text-sm font-bold">{{ strtoupper(substr($partner->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $partner->name }}</p>
                                            @if($partner->notes)
                                            <p class="text-xs text-base-content/50 truncate max-w-xs">{{ Str::limit($partner->notes, 50) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $partner->email }}" class="link link-primary text-sm">{{ $partner->email }}</a>
                                </td>
                                <td class="text-right">
                                    <span class="font-mono text-lg font-semibold">{{ number_format($partner->percentage, 2) }}%</span>
                                </td>
                                <td>
                                    @if($partner->is_active)
                                        <span class="badge badge-success badge-soft badge-sm">Active</span>
                                    @else
                                        <span class="badge badge-error badge-soft badge-sm">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1 justify-end">
                                        <a href="{{ route('backoffice.partners.edit', $partner) }}" class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <form action="{{ route('backoffice.partners.toggle-status', $partner) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-ghost btn-sm btn-circle" title="{{ $partner->is_active ? 'Deactivate' : 'Activate' }}">
                                                <span class="icon-[tabler--{{ $partner->is_active ? 'toggle-right' : 'toggle-left' }}] size-4 {{ $partner->is_active ? 'text-success' : 'text-base-content/50' }}"></span>
                                            </button>
                                        </form>
                                        <form action="{{ route('backoffice.partners.destroy', $partner) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this partner?')">
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
                        <tfoot>
                            <tr class="bg-base-200/50">
                                <td colspan="2" class="font-semibold">Total</td>
                                <td class="text-right font-mono text-lg font-bold text-primary">{{ number_format($totalPercentage, 2) }}%</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
