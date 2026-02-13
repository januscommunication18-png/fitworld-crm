@extends('layouts.settings')

@section('title', $questionnaire->name . ' — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.index') }}">Questionnaires</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $questionnaire->name }}</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('questionnaires.index') }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold">{{ $questionnaire->name }}</h1>
                    <span class="badge {{ \App\Models\Questionnaire::getStatusBadgeClass($questionnaire->status) }}">
                        {{ ucfirst($questionnaire->status) }}
                    </span>
                </div>
                <p class="text-base-content/60 mt-1">
                    {{ \App\Models\Questionnaire::getTypes()[$questionnaire->type] }}
                    @if($questionnaire->estimated_minutes)
                        &bull; ~{{ $questionnaire->estimated_minutes }} min
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('questionnaires.preview', $questionnaire) }}" class="btn btn-ghost">
                <span class="icon-[tabler--eye] size-5"></span>
                Preview
            </a>
            <a href="{{ route('questionnaires.builder', $questionnaire) }}" class="btn btn-ghost">
                <span class="icon-[tabler--edit] size-5"></span>
                Edit Questions
            </a>
            @if($questionnaire->isDraft())
                <form action="{{ route('questionnaires.publish', $questionnaire) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--rocket] size-5"></span>
                        Publish
                    </button>
                </form>
            @endif
            <div class="relative">
                <details class="dropdown dropdown-bottom dropdown-end">
                    <summary class="btn btn-ghost btn-square list-none cursor-pointer">
                        <span class="icon-[tabler--dots-vertical] size-5"></span>
                    </summary>
                    <ul class="dropdown-content menu bg-base-100 rounded-box w-48 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                        <li><a href="{{ route('questionnaires.edit', $questionnaire) }}">
                            <span class="icon-[tabler--settings] size-4"></span> Settings
                        </a></li>
                        <li>
                            <form action="{{ route('questionnaires.duplicate', $questionnaire) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2">
                                    <span class="icon-[tabler--copy] size-4"></span> Duplicate
                                </button>
                            </form>
                        </li>
                        @if($questionnaire->isActive())
                            <li><a href="javascript:void(0)" onclick="event.preventDefault(); this.closest('li').querySelector('form').submit();" class="text-warning">
                                <span class="icon-[tabler--archive] size-4"></span> Archive
                                <form action="{{ route('questionnaires.unpublish', $questionnaire) }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </a></li>
                        @endif
                    </ul>
                </details>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary/10">
                        <span class="icon-[tabler--send] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $responseStats['total'] }}</p>
                        <p class="text-sm text-base-content/60">Total Sent</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-success/10">
                        <span class="icon-[tabler--circle-check] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $responseStats['completed'] }}</p>
                        <p class="text-sm text-base-content/60">Completed</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-warning/10">
                        <span class="icon-[tabler--clock] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $responseStats['pending'] }}</p>
                        <p class="text-sm text-base-content/60">Pending</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if($questionnaire->description)
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <p class="text-base-content/70">{{ $questionnaire->description }}</p>
            </div>
        </div>
    @endif

    {{-- Responses Section --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Responses</h2>
                @if($responseStats['total'] > 0)
                    <a href="{{ route('questionnaires.responses', $questionnaire) }}" class="btn btn-sm btn-ghost">
                        View All
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                @endif
            </div>

            @if($responseStats['total'] === 0)
                <div class="text-center py-12">
                    <span class="icon-[tabler--inbox] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="text-lg font-semibold mb-2">No Responses Yet</h3>
                    <p class="text-base-content/60 max-w-md mx-auto">
                        @if($questionnaire->isDraft())
                            Publish this questionnaire to start collecting responses from clients.
                        @else
                            Attach this questionnaire to a class or service plan, or send it directly to clients.
                        @endif
                    </p>
                    @if($questionnaire->isDraft())
                        <form action="{{ route('questionnaires.publish', $questionnaire) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--rocket] size-5"></span>
                                Publish Questionnaire
                            </button>
                        </form>
                    @elseif($questionnaire->isActive())
                        <button type="button" class="btn btn-primary mt-4" onclick="document.getElementById('send-modal').showModal()">
                            <span class="icon-[tabler--send] size-5"></span>
                            Send to Client
                        </button>
                    @endif
                </div>
            @else
                {{-- Quick actions --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    <a href="{{ route('questionnaires.responses', $questionnaire) }}" class="btn btn-sm btn-outline">
                        <span class="icon-[tabler--list] size-4"></span>
                        View All Responses
                    </a>
                    @if($questionnaire->isActive())
                        <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('send-modal').showModal()">
                            <span class="icon-[tabler--send] size-4"></span>
                            Send to Client
                        </button>
                    @endif
                </div>

                {{-- Response summary --}}
                <div class="bg-base-200/50 rounded-lg p-4">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-primary">{{ $responseStats['total'] }}</p>
                            <p class="text-xs text-base-content/60">Total</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-success">{{ $responseStats['completed'] }}</p>
                            <p class="text-xs text-base-content/60">Completed</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-warning">{{ $responseStats['pending'] }}</p>
                            <p class="text-xs text-base-content/60">Pending</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Version History --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Version History</h2>
            </div>

            @php
                $versions = $questionnaire->versions()->orderByDesc('version_number')->get();
            @endphp

            @if($versions->isEmpty())
                <p class="text-base-content/60 text-center py-4">No versions yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Questions</th>
                                <th>Created</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($versions as $version)
                                <tr>
                                    <td class="font-medium">v{{ $version->version_number }}</td>
                                    <td>
                                        @if($version->isActive())
                                            <span class="badge badge-success badge-sm">Active</span>
                                        @elseif($version->isDraft())
                                            <span class="badge badge-warning badge-sm">Draft</span>
                                        @else
                                            <span class="badge badge-ghost badge-sm">Archived</span>
                                        @endif
                                    </td>
                                    <td>{{ $version->getTotalQuestionCount() }}</td>
                                    <td class="text-sm">{{ $version->created_at->format('M j, Y') }}</td>
                                    <td class="text-sm">
                                        @if($version->published_at)
                                            {{ $version->published_at->format('M j, Y') }}
                                        @else
                                            <span class="text-base-content/40">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Attachments Section --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Attached To</h2>
            </div>

            @if($questionnaire->attachments->isEmpty())
                <div class="text-center py-8">
                    <span class="icon-[tabler--link-off] size-12 text-base-content/20 mx-auto mb-3"></span>
                    <p class="text-base-content/60">
                        This questionnaire is not attached to any class plans, service plans, or memberships yet.
                    </p>
                    <p class="text-sm text-base-content/40 mt-2">
                        Attach it from the plan edit page to require it for bookings.
                    </p>
                </div>
            @else
                <ul class="divide-y divide-base-content/10">
                    @foreach($questionnaire->attachments as $attachment)
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--link] size-5 text-base-content/40"></span>
                                <div>
                                    <p class="font-medium">{{ $attachment->attachable?->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-base-content/60">{{ $attachment->getAttachableTypeName() }}</p>
                                </div>
                            </div>
                            <span class="badge badge-soft badge-sm">
                                {{ $attachment->is_required ? 'Required' : 'Optional' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

{{-- Send to Client Modal --}}
<dialog id="send-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Send Questionnaire to Client</h3>

        {{-- SMS Coming Soon Notice --}}
        <div class="alert alert-info mb-4">
            <span class="icon-[tabler--message] size-5"></span>
            <div>
                <p class="font-medium">SMS Coming Soon</p>
                <p class="text-sm">Email delivery is available now. SMS notifications will be added in a future update.</p>
            </div>
        </div>

        <form action="{{ route('questionnaires.responses.create', $questionnaire) }}" method="POST">
            @csrf
            <div class="form-control">
                <label class="label" for="client_id">
                    <span class="label-text">Select Client</span>
                </label>
                <select name="client_id" id="client_id" class="select select-bordered" required>
                    <option value="">Choose a client...</option>
                    @php
                        $host = auth()->user()->currentHost() ?? auth()->user()->host;
                        $clients = $host->clients()->orderBy('last_name')->get();
                    @endphp
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->full_name }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text">Delivery Method</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer p-3 border border-base-300 rounded-lg hover:bg-base-200">
                        <input type="radio" name="delivery_method" value="link" class="radio radio-primary" checked>
                        <div>
                            <span class="font-medium">Generate Link Only</span>
                            <p class="text-xs text-base-content/60">Get a shareable link to send manually</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 cursor-not-allowed p-3 border border-base-300 rounded-lg opacity-50">
                        <input type="radio" name="delivery_method" value="email" class="radio" disabled>
                        <div>
                            <span class="font-medium">Send via Email</span>
                            <p class="text-xs text-base-content/60">Coming soon</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 cursor-not-allowed p-3 border border-base-300 rounded-lg opacity-50">
                        <input type="radio" name="delivery_method" value="sms" class="radio" disabled>
                        <div>
                            <span class="font-medium">Send via SMS</span>
                            <p class="text-xs text-base-content/60">Coming soon</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('send-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--link] size-4"></span>
                    Generate Link
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Response URL Flash --}}
@if(session('response_url'))
    <dialog id="link-modal" class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">
                <span class="icon-[tabler--check] size-5 text-success me-2"></span>
                Link Generated!
            </h3>
            <p class="text-base-content/70 mb-4">Share this link with your client to complete the questionnaire:</p>
            <div class="flex items-center gap-2">
                <input type="text" value="{{ session('response_url') }}" class="input input-bordered flex-1" readonly id="response-url-input">
                <button type="button" class="btn btn-primary" onclick="copyToClipboard()">
                    <span class="icon-[tabler--copy] size-4"></span>
                    Copy
                </button>
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="document.getElementById('link-modal').close()">Close</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endif

@push('scripts')
<script>
function copyToClipboard() {
    const input = document.getElementById('response-url-input');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        alert('Link copied to clipboard!');
    });
}
</script>
@endpush
@endsection
