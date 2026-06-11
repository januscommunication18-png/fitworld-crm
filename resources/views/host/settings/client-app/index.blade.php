@extends('layouts.settings')

@section('title', 'Client App — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Client App</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold">Branded Client App</h1>
        <p class="text-base-content/60 mt-1">Configure your studio's white-label mobile app: branding, onboarding, and the app token baked into your build. Login method and member features are managed under <a href="{{ route('settings.member-portal') }}" class="link link-primary">Client &amp; Portal Settings</a>.</p>
    </div>

    {{-- ═══ App Token ═══ --}}
    <div class="bg-base-100 rounded-lg p-5 space-y-4">
        <div class="flex items-center gap-2">
            <span class="icon-[tabler--key] size-5 text-primary"></span>
            <span class="text-lg font-semibold">Studio App Token</span>
        </div>
        <p class="text-sm text-base-content/60">This token identifies your studio inside your branded app build. It is not a user credential — clients still sign in with their Client ID.</p>
        <div class="flex items-center gap-2">
            <input type="text" id="client-app-token" class="input input-bordered w-full font-mono text-sm" readonly
                   value="{{ $host->client_app_token ?? '' }}"
                   placeholder="Generated when you enable the client app">
            {{-- Copy / Regenerate disabled for now
            <button type="button" class="btn btn-soft" id="copy-token-btn" {{ $host->client_app_token ? '' : 'disabled' }}>
                <span class="icon-[tabler--copy] size-4"></span> Copy
            </button>
            <button type="button" class="btn btn-soft btn-warning" id="regen-token-btn" {{ $host->client_app_token ? '' : 'disabled' }}>
                <span class="icon-[tabler--refresh] size-4"></span> Regenerate
            </button>
            --}}
        </div>
        <p class="text-xs text-warning hidden" id="regen-warning">Regenerating invalidates the token in existing app builds — they must be rebuilt with the new token.</p>
    </div>

    {{-- ═══ App Settings ═══ --}}
    <form id="client-app-form" class="bg-base-100 rounded-lg p-5 space-y-6">
        @csrf

        <div class="flex items-center justify-between">
            <div>
                <span class="text-lg font-semibold">App Configuration</span>
                <p class="text-sm text-base-content/60">Branding shown inside the app. Colors and names fall back to your booking page settings when left empty.</p>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <span class="label-text font-medium">Enabled</span>
                <input type="checkbox" id="app_enabled" class="switch switch-primary"
                       {{ ($settings['enabled'] ?? false) ? 'checked' : '' }}>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="label-text" for="code_prefix">Client ID Prefix</label>
                <input type="text" id="code_prefix" maxlength="5"
                       value="{{ $settings['code_prefix'] ?? '' }}"
                       class="input input-bordered w-full mt-1 uppercase font-mono">
                <p class="text-xs text-base-content/50 mt-1">2–5 letters. Client IDs look like <span class="font-mono">{{ $settings['code_prefix'] ?? 'ZYS' }}-SARAH-84922</span>. Must be unique across studios.</p>
            </div>
            <div>
                <label class="label-text" for="app_display_name">App Display Name</label>
                <input type="text" id="app_display_name" maxlength="60"
                       value="{{ $settings['app_display_name'] ?? '' }}"
                       placeholder="{{ $host->studio_name }}"
                       class="input input-bordered w-full mt-1">
            </div>
            <div>
                <label class="label-text" for="primary_color">Primary Color</label>
                <div class="flex items-center gap-2 mt-1">
                    <input type="color" id="primary_color_picker"
                           value="{{ $settings['primary_color'] ?? ($host->booking_settings['primary_color'] ?? '#023E8A') }}"
                           class="h-10 w-12 rounded cursor-pointer border border-base-300">
                    <input type="text" id="primary_color"
                           value="{{ $settings['primary_color'] ?? '' }}"
                           placeholder="{{ $host->booking_settings['primary_color'] ?? '#023E8A' }}"
                           class="input input-bordered w-full font-mono">
                </div>
            </div>
            <div>
                <label class="label-text" for="app_theme">Theme</label>
                <select id="app_theme" class="select select-bordered w-full mt-1">
                    <option value="" {{ empty($settings['theme']) ? 'selected' : '' }}>Inherit from booking page</option>
                    <option value="light" {{ ($settings['theme'] ?? '') === 'light' ? 'selected' : '' }}>Light</option>
                    <option value="dark" {{ ($settings['theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark</option>
                    <option value="auto" {{ ($settings['theme'] ?? '') === 'auto' ? 'selected' : '' }}>Auto</option>
                </select>
            </div>
            <div>
                <label class="label-text" for="support_email">Support Email</label>
                <input type="email" id="support_email"
                       value="{{ $settings['support_email'] ?? '' }}"
                       class="input input-bordered w-full mt-1">
            </div>
            <div>
                <label class="label-text" for="support_phone">Support Phone</label>
                <input type="text" id="support_phone" maxlength="50"
                       value="{{ $settings['support_phone'] ?? '' }}"
                       class="input input-bordered w-full mt-1">
            </div>
        </div>

        {{-- Onboarding slides --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <div>
                    <span class="font-semibold">Onboarding Slides</span>
                    <p class="text-xs text-base-content/50">Up to 5 intro screens shown the first time a client opens the app.</p>
                </div>
                <button type="button" class="btn btn-soft btn-sm" id="add-slide-btn">
                    <span class="icon-[tabler--plus] size-4"></span> Add slide
                </button>
            </div>
            <div id="slides-container" class="space-y-3">
                @foreach(($settings['onboarding_slides'] ?? []) as $slide)
                    <div class="slide-row border border-base-300 rounded-lg p-3 space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="text" maxlength="80" placeholder="Title"
                                   value="{{ $slide['title'] ?? '' }}"
                                   class="slide-title input input-bordered input-sm w-full">
                            <button type="button" class="btn btn-soft btn-error btn-sm remove-slide-btn">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </div>
                        <textarea maxlength="300" rows="2" placeholder="Body text"
                                  class="slide-body textarea textarea-bordered w-full text-sm">{{ $slide['body'] ?? '' }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" class="btn btn-primary" id="save-app-btn">
                <span class="icon-[tabler--device-floppy] size-4"></span> Save Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
var copyTokenBtn = document.getElementById('copy-token-btn');
if (copyTokenBtn) copyTokenBtn.addEventListener('click', function() {
    var input = document.getElementById('client-app-token');
    navigator.clipboard.writeText(input.value);
    showToast('Token copied to clipboard', 'success');
});

var regenTokenBtn = document.getElementById('regen-token-btn');
if (regenTokenBtn) regenTokenBtn.addEventListener('click', async function() {
    if (!confirm('Regenerate the app token? Existing app builds will stop working until rebuilt with the new token.')) return;
    var btn = this;
    btn.disabled = true;
    try {
        var response = await fetch('{{ route('settings.client-app.regenerate-token') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
        var result = await response.json();
        if (result.success) {
            document.getElementById('client-app-token').value = result.client_app_token;
            document.getElementById('regen-warning').classList.remove('hidden');
        }
        showToast(result.message || 'Done', result.success ? 'success' : 'error');
    } catch (e) {
        showToast('An error occurred', 'error');
    } finally {
        btn.disabled = false;
    }
});

// Color picker <-> hex input sync.
document.getElementById('primary_color_picker').addEventListener('input', function() {
    document.getElementById('primary_color').value = this.value.toUpperCase();
});

// Onboarding slide rows.
document.getElementById('add-slide-btn').addEventListener('click', function() {
    var container = document.getElementById('slides-container');
    if (container.querySelectorAll('.slide-row').length >= 5) {
        showToast('Maximum 5 slides', 'error');
        return;
    }
    var row = document.createElement('div');
    row.className = 'slide-row border border-base-300 rounded-lg p-3 space-y-2';
    row.innerHTML = '<div class="flex items-center gap-2">' +
        '<input type="text" maxlength="80" placeholder="Title" class="slide-title input input-bordered input-sm w-full">' +
        '<button type="button" class="btn btn-soft btn-error btn-sm remove-slide-btn"><span class="icon-[tabler--trash] size-4"></span></button>' +
        '</div>' +
        '<textarea maxlength="300" rows="2" placeholder="Body text" class="slide-body textarea textarea-bordered w-full text-sm"></textarea>';
    container.appendChild(row);
});

document.getElementById('slides-container').addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-slide-btn');
    if (btn) btn.closest('.slide-row').remove();
});

// Save.
document.getElementById('save-app-btn').addEventListener('click', async function() {
    var btn = this;
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';

    var slides = [];
    document.querySelectorAll('#slides-container .slide-row').forEach(function(row) {
        var title = row.querySelector('.slide-title').value.trim();
        if (!title) return;
        slides.push({ title: title, body: row.querySelector('.slide-body').value.trim() });
    });

    var data = {
        enabled: document.getElementById('app_enabled').checked,
        code_prefix: document.getElementById('code_prefix').value.trim().toUpperCase(),
        app_display_name: document.getElementById('app_display_name').value.trim() || null,
        primary_color: document.getElementById('primary_color').value.trim() || null,
        theme: document.getElementById('app_theme').value || null,
        support_email: document.getElementById('support_email').value.trim() || null,
        support_phone: document.getElementById('support_phone').value.trim() || null,
        onboarding_slides: slides,
    };

    try {
        var response = await fetch('{{ route('settings.client-app.update') }}', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(data),
        });
        var result = await response.json();
        if (result.success && result.client_app_token) {
            document.getElementById('client-app-token').value = result.client_app_token;
            if (copyTokenBtn) copyTokenBtn.disabled = false;
            if (regenTokenBtn) regenTokenBtn.disabled = false;
        }
        showToast(result.message || (result.success ? 'Saved' : 'Failed to save'), result.success ? 'success' : 'error');
    } catch (e) {
        showToast('An error occurred while saving', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});
</script>
@endpush
@endsection
