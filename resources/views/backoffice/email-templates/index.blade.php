@extends('backoffice.layouts.app')

@section('title', 'Email Templates')
@section('page-title', 'Email Templates')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <p class="text-base-content/60">Manage system and client email templates.</p>
        </div>
        <a href="{{ route('backoffice.email-templates.create', ['host_id' => $tab === 'client' ? $hostId : null]) }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Template
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="tabs tabs-bordered">
            <a href="{{ route('backoffice.email-templates.index', ['tab' => 'system']) }}"
               class="tab {{ $tab === 'system' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--settings] size-4 mr-2"></span>
                FitCRM Templates
            </a>
            <a href="{{ route('backoffice.email-templates.index', ['tab' => 'client', 'host_id' => $hostId]) }}"
               class="tab {{ $tab === 'client' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--building] size-4 mr-2"></span>
                Client Templates
            </a>
        </div>

        @if($tab === 'client')
        <form action="{{ route('backoffice.email-templates.index') }}" method="GET" class="w-full sm:w-72">
            <input type="hidden" name="tab" value="client">
            <select name="host_id" class="select w-full" onchange="this.form.submit()">
                <option value="">Select a client...</option>
                @foreach($hosts as $host)
                    <option value="{{ $host->id }}" {{ $hostId == $host->id ? 'selected' : '' }}>
                        {{ $host->studio_name }}
                    </option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    {{-- Templates List --}}
    @if($tab === 'client' && !$hostId)
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--building] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">Select a Client</h3>
                <p class="text-base-content/60">Choose a client from the dropdown above to view their email templates.</p>
            </div>
        </div>
    @elseif($templates->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--mail-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">No Templates Yet</h3>
                <p class="text-base-content/60 mb-4">Create your first email template to get started.</p>
                <a href="{{ route('backoffice.email-templates.create', ['host_id' => $tab === 'client' ? $hostId : null]) }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create Template
                </a>
            </div>
        </div>
    @else
        @foreach($templates as $category => $categoryTemplates)
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title capitalize">{{ $category }} Templates</h3>
                <span class="badge badge-neutral badge-sm">{{ $categoryTemplates->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Key</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th class="w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryTemplates as $template)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $template->name }}</div>
                                    @if($template->is_default)
                                        <span class="badge badge-soft badge-info badge-xs">Default</span>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $template->key }}</code>
                                </td>
                                <td class="max-w-xs truncate">{{ $template->subject }}</td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge badge-soft badge-success badge-sm">Active</span>
                                    @else
                                        <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('backoffice.email-templates.preview', $template) }}"
                                           class="btn btn-ghost btn-xs btn-square" title="Preview">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('backoffice.email-templates.edit', $template) }}"
                                           class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <form action="{{ route('backoffice.email-templates.duplicate', $template) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square" title="Duplicate">
                                                <span class="icon-[tabler--copy] size-4"></span>
                                            </button>
                                        </form>
                                        <form action="{{ route('backoffice.email-templates.destroy', $template) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this template?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
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
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection
