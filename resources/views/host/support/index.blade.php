@extends('layouts.settings')

@section('title', 'Support Requests')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Support Requests</li>
    </ol>
@endsection

@section('settings-content')
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

{{-- Support Request Modal (reuse from setup checklist) --}}
<div id="support-request-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;" role="dialog" tabindex="-1">
    <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center">
                    <span class="icon-[tabler--headset] size-5 text-secondary"></span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Request Technical Support</h3>
                    <p class="text-xs text-base-content/50">Our team will respond within 24 hours</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeSupportModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <form id="support-request-form" class="p-4 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label-text text-sm font-medium" for="support_first_name">First Name <span class="text-error">*</span></label>
                    <input type="text" id="support_first_name" name="first_name" class="input input-bordered w-full mt-1" value="{{ auth()->user()->first_name ?? '' }}" required />
                </div>
                <div>
                    <label class="label-text text-sm font-medium" for="support_last_name">Last Name <span class="text-error">*</span></label>
                    <input type="text" id="support_last_name" name="last_name" class="input input-bordered w-full mt-1" value="{{ auth()->user()->last_name ?? '' }}" required />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label-text text-sm font-medium" for="support_email">Email Address <span class="text-error">*</span></label>
                    <input type="email" id="support_email" name="email" class="input input-bordered w-full mt-1" value="{{ auth()->user()->email ?? '' }}" required />
                </div>
                <div>
                    <label class="label-text text-sm font-medium" for="support_phone">Phone Number</label>
                    <input type="tel" id="support_phone" name="phone" class="input input-bordered w-full mt-1" value="{{ auth()->user()->phone ?? '' }}" />
                </div>
            </div>

            <div>
                <label class="label-text text-sm font-medium" for="support_note">How can we help? <span class="text-error">*</span></label>
                <textarea id="support_note" name="note" class="textarea textarea-bordered w-full mt-1" rows="4" placeholder="Please describe your issue or question in detail..." required></textarea>
            </div>

            <div id="support-error" class="alert alert-error hidden">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span id="support-error-text"></span>
            </div>
            <div id="support-success" class="alert alert-success hidden">
                <span class="icon-[tabler--check] size-5"></span>
                <span id="support-success-text"></span>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeSupportModal()">Cancel</button>
                <button type="submit" id="submit-support-btn" class="btn btn-secondary">
                    <span class="icon-[tabler--send] size-4"></span>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openSupportModal() {
    const modal = document.getElementById('support-request-modal');
    if (modal) {
        modal.setAttribute('style', 'display: flex !important; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;');
        document.body.style.overflow = 'hidden';
    }
}

function closeSupportModal() {
    const modal = document.getElementById('support-request-modal');
    if (modal) {
        modal.setAttribute('style', 'display: none;');
        document.body.style.overflow = '';
        document.getElementById('support-request-form').reset();
        document.getElementById('support-error').classList.add('hidden');
        document.getElementById('support-success').classList.add('hidden');
    }
}

document.getElementById('support-request-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('submit-support-btn');
    const errorDiv = document.getElementById('support-error');
    const errorText = document.getElementById('support-error-text');
    const successDiv = document.getElementById('support-success');
    const successText = document.getElementById('support-success-text');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    const firstName = document.getElementById('support_first_name').value.trim();
    const lastName = document.getElementById('support_last_name').value.trim();
    const email = document.getElementById('support_email').value.trim();
    const phone = document.getElementById('support_phone').value.trim();
    const note = document.getElementById('support_note').value.trim();

    if (!firstName || !lastName || !email || !note) {
        errorText.textContent = 'Please fill in all required fields.';
        errorDiv.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Submitting...';

    try {
        const response = await fetch('{{ route("support.requests.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ first_name: firstName, last_name: lastName, email: email, phone: phone, note: note })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            successText.textContent = data.message;
            successDiv.classList.remove('hidden');
            setTimeout(() => { closeSupportModal(); window.location.reload(); }, 2000);
        } else {
            errorText.textContent = data.message || 'Failed to submit request.';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorText.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Submit Request';
    }
});
</script>
@endpush
@endsection
