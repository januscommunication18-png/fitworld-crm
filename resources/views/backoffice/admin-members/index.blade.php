@extends('backoffice.layouts.app')

@section('title', 'Admin Members')
@section('page-title', 'Admin Members')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <p class="text-base-content/60">Manage admin users and their permissions.</p>
        </div>
        <a href="{{ route('backoffice.admin-members.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--user-plus] size-5"></span>
            Invite Admin
        </a>
    </div>

    {{-- Members Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary/10 text-primary size-10 rounded-full text-sm font-bold">
                                            {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $member->first_name }} {{ $member->last_name }}</div>
                                        @if($member->id === auth('admin')->id())
                                            <span class="badge badge-soft badge-info badge-xs">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>
                                @if($member->role === 'administrator')
                                    <span class="badge badge-soft badge-primary badge-sm">Administrator</span>
                                @else
                                    <span class="badge badge-soft badge-neutral badge-sm">Team Member</span>
                                @endif
                            </td>
                            <td>
                                @if($member->status === 'active')
                                    <span class="badge badge-soft badge-success badge-sm">Active</span>
                                @elseif($member->status === 'suspended')
                                    <span class="badge badge-soft badge-warning badge-sm">Suspended</span>
                                @else
                                    <span class="badge badge-soft badge-neutral badge-sm capitalize">{{ $member->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($member->last_login_at)
                                    <div class="text-sm">{{ $member->last_login_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-base-content/60">{{ $member->last_login_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-base-content/40">Never</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('backoffice.admin-members.edit', $member) }}"
                                       class="btn btn-ghost btn-xs btn-square" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </a>

                                    @if($member->id !== auth('admin')->id())
                                    <form action="{{ route('backoffice.admin-members.toggle-status', $member) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square"
                                                title="{{ $member->status === 'active' ? 'Suspend' : 'Reactivate' }}">
                                            @if($member->status === 'active')
                                                <span class="icon-[tabler--user-off] size-4 text-warning"></span>
                                            @else
                                                <span class="icon-[tabler--user-check] size-4 text-success"></span>
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('backoffice.admin-members.reset-password', $member) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Reset password for {{ $member->first_name }}? They will receive a new password via email.')">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square" title="Reset Password">
                                            <span class="icon-[tabler--key] size-4"></span>
                                        </button>
                                    </form>

                                    <form action="{{ route('backoffice.admin-members.destroy', $member) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this admin member?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-base-content/60">
                                    <span class="icon-[tabler--users-off] size-12 opacity-30"></span>
                                    <p>No admin members found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Roles Explanation --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon-[tabler--shield-check] size-5 text-primary"></span>
                    Administrator
                </h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60">
                    Full access to all features and settings. Can manage other admin members and system configuration.
                </p>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon-[tabler--user] size-5 text-base-content/40"></span>
                    Team Member
                </h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-base-content/60">
                    Limited access based on assigned permissions. Cannot access Plans, Invoice, Admin Members, or Settings by default.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
