@extends('layouts.settings')

@section('title', 'Responses: ' . $questionnaire->name . ' — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.index') }}">Questionnaires</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Responses</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('questionnaires.show', $questionnaire) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Responses</h1>
                <p class="text-base-content/60 mt-1">{{ $questionnaire->name }}</p>
            </div>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('send-modal').showModal()">
            <span class="icon-[tabler--send] size-4"></span>
            Send to Client
        </button>
    </div>

    {{-- Response URL Flash --}}
    @if(session('response_url'))
        <div class="alert alert-success">
            <span class="icon-[tabler--link] size-5"></span>
            <div class="flex-1">
                <p class="font-medium">Response link ready:</p>
                <div class="flex items-center gap-2 mt-1">
                    <input type="text" value="{{ session('response_url') }}" class="input input-sm input-bordered flex-1" readonly id="response-url-input">
                    <button type="button" class="btn btn-sm btn-ghost" onclick="copyToClipboard()">
                        <span class="icon-[tabler--copy] size-4"></span>
                        Copy
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-base-200">
                        <span class="icon-[tabler--list] size-5"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['all'] }}</p>
                        <p class="text-sm text-base-content/60">Total Responses</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-success/20">
                        <span class="icon-[tabler--check] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['completed'] }}</p>
                        <p class="text-sm text-base-content/60">Completed</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-warning/20">
                        <span class="icon-[tabler--clock] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $counts['pending'] }}</p>
                        <p class="text-sm text-base-content/60">Pending</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex gap-2">
        <a href="{{ route('questionnaires.responses', $questionnaire) }}"
           class="badge {{ !$status ? 'badge-primary' : 'badge-ghost' }}">
            All ({{ $counts['all'] }})
        </a>
        <a href="{{ route('questionnaires.responses', [$questionnaire, 'status' => 'completed']) }}"
           class="badge {{ $status === 'completed' ? 'badge-primary' : 'badge-ghost' }}">
            Completed ({{ $counts['completed'] }})
        </a>
        <a href="{{ route('questionnaires.responses', [$questionnaire, 'status' => 'pending']) }}"
           class="badge {{ $status === 'pending' || $status === 'in_progress' ? 'badge-primary' : 'badge-ghost' }}">
            Pending ({{ $counts['pending'] }})
        </a>
    </div>

    {{-- Responses Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            @if($responses->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--inbox] size-16 text-base-content/20 mx-auto block mb-4"></span>
                    <p class="text-base-content/60">No responses yet.</p>
                    <p class="text-sm text-base-content/40 mt-1">Send this questionnaire to clients to start collecting responses.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Started</th>
                                <th>Completed</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($responses as $response)
                                <tr class="hover">
                                    <td>
                                        @if($response->client)
                                            <div class="flex items-center gap-3">
                                                <div class="avatar avatar-placeholder">
                                                    <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                                        {{ strtoupper(substr($response->client->first_name, 0, 1) . substr($response->client->last_name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-medium">{{ $response->client->full_name }}</div>
                                                    <div class="text-sm text-base-content/60">{{ $response->client->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-base-content/50">Anonymous</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ \App\Models\QuestionnaireResponse::getStatusBadgeClass($response->status) }}">
                                            {{ $statuses[$response->status] ?? $response->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($response->started_at)
                                            <span class="text-sm">{{ $response->started_at->format('M j, Y g:i A') }}</span>
                                        @else
                                            <span class="text-sm text-base-content/40">Not started</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($response->completed_at)
                                            <span class="text-sm">{{ $response->completed_at->format('M j, Y g:i A') }}</span>
                                        @else
                                            <span class="text-sm text-base-content/40">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" class="btn btn-ghost btn-sm btn-square">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </button>
                                            <ul tabindex="0" class="dropdown-menu dropdown-menu-end w-52">
                                                <li>
                                                    <a href="{{ route('questionnaires.responses.show', [$questionnaire, $response]) }}">
                                                        <span class="icon-[tabler--eye] size-4"></span>
                                                        View Response
                                                    </a>
                                                </li>
                                                @if(!$response->isCompleted())
                                                    <li>
                                                        <button type="button" onclick="copyLink('{{ $response->getResponseUrl() }}')">
                                                            <span class="icon-[tabler--link] size-4"></span>
                                                            Copy Link
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('questionnaires.responses.resend', [$questionnaire, $response]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit">
                                                                <span class="icon-[tabler--refresh] size-4"></span>
                                                                Regenerate Link
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($responses->hasPages())
                    <div class="px-4 py-3 border-t border-base-200">
                        {{ $responses->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Send Modal --}}
<dialog id="send-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Send Questionnaire to Client</h3>
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
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('send-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--send] size-4"></span>
                    Generate Link
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script>
function copyToClipboard() {
    const input = document.getElementById('response-url-input');
    input.select();
    document.execCommand('copy');
    // Show toast or feedback
    alert('Link copied to clipboard!');
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    });
}
</script>
@endpush
@endsection
