@extends('layouts.support')

@section('title', 'Support Requests')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">My Support Requests</h1>
            <p class="text-base-content/60 text-sm">Track the status of your support requests</p>
        </div>
        <button type="button" onclick="openSupportModal()" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] size-4"></span>
            New Request
        </button>
    </div>

    {{-- Support Requests List --}}
    @if($supportRequests->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--message-circle] size-8 text-base-content/30"></span>
                </div>
                <h3 class="font-semibold text-lg mb-2">No Support Requests</h3>
                <p class="text-base-content/60 mb-4">You haven't submitted any support requests yet.</p>
                <button type="button" onclick="openSupportModal()" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--headset] size-4"></span>
                    Request Technical Support
                </button>
            </div>
        </div>
    @else
        <div class="card bg-base-100">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Last Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supportRequests as $request)
                        <tr class="hover">
                            <td class="font-mono text-sm">#{{ $request->id }}</td>
                            <td>
                                <div class="max-w-xs">
                                    <p class="font-medium truncate">{{ Str::limit($request->note, 50) }}</p>
                                    <p class="text-xs text-base-content/50">{{ $request->full_name }} &middot; {{ $request->email }}</p>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $request->status_badge_class }} badge-sm">
                                    {{ $request->status_label }}
                                </span>
                            </td>
                            <td class="text-sm text-base-content/70">
                                {{ $request->created_at->format('M d, Y') }}
                                <br>
                                <span class="text-xs text-base-content/50">{{ $request->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="text-sm text-base-content/70">
                                {{ $request->updated_at->diffForHumans() }}
                            </td>
                            <td>
                                <a href="{{ route('support.requests.show', $request) }}" class="btn btn-ghost btn-sm btn-circle">
                                    <span class="icon-[tabler--eye] size-4"></span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($supportRequests->hasPages())
        <div class="flex justify-center">
            {{ $supportRequests->links() }}
        </div>
        @endif
    @endif
</div>

<x-support-drawer />
@endsection
