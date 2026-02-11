@extends('layouts.dashboard')

@section('title', 'Lead Magnet')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--magnet] me-1 size-4"></span> Lead Magnet</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">Lead Magnet</h1>
        <p class="text-base-content/60 mt-1">Capture leads directly from your website with custom web forms.</p>
    </div>

    {{-- Coming Soon Banner --}}
    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20">
        <div class="card-body items-center text-center py-12">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-primary/20 mb-6">
                <span class="icon-[tabler--magnet] size-12 text-primary"></span>
            </div>

            <span class="badge badge-lg badge-primary badge-soft mb-4">
                <span class="icon-[tabler--clock] size-4 mr-1"></span>
                Coming Soon
            </span>

            <h2 class="text-2xl font-bold mb-2">Lead Capture Made Easy</h2>
            <p class="text-lg text-base-content/60 max-w-xl">
                Create beautiful lead capture forms that integrate seamlessly with your CRM.
                Capture prospects from your website, social media, and marketing campaigns.
            </p>
        </div>
    </div>

    {{-- Features Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="bg-primary/10 rounded-lg p-3 w-fit mb-4">
                    <span class="icon-[tabler--forms] size-6 text-primary"></span>
                </div>
                <h3 class="card-title text-lg">Simple Form Builder</h3>
                <p class="text-base-content/60">Create lead capture forms with name, email, phone, and custom fields tailored to your business.</p>
            </div>
        </div>

        <div class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="bg-success/10 rounded-lg p-3 w-fit mb-4">
                    <span class="icon-[tabler--link] size-6 text-success"></span>
                </div>
                <h3 class="card-title text-lg">Hosted Form URLs</h3>
                <p class="text-base-content/60">Each form gets a unique URL on your studio subdomain. Share anywhere to capture leads.</p>
            </div>
        </div>

        <div class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="bg-warning/10 rounded-lg p-3 w-fit mb-4">
                    <span class="icon-[tabler--user-plus] size-6 text-warning"></span>
                </div>
                <h3 class="card-title text-lg">Automatic Lead Creation</h3>
                <p class="text-base-content/60">Form submissions automatically create new leads in your CRM with all captured information.</p>
            </div>
        </div>

        <div class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="bg-info/10 rounded-lg p-3 w-fit mb-4">
                    <span class="icon-[tabler--chart-line] size-6 text-info"></span>
                </div>
                <h3 class="card-title text-lg">UTM Tracking</h3>
                <p class="text-base-content/60">Automatically capture marketing campaign data for attribution. Know exactly where your leads come from.</p>
            </div>
        </div>

        <div class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="bg-secondary/10 rounded-lg p-3 w-fit mb-4">
                    <span class="icon-[tabler--tags] size-6 text-secondary"></span>
                </div>
                <h3 class="card-title text-lg">Auto-Tagging</h3>
                <p class="text-base-content/60">Automatically tag leads based on the form they submitted. Segment and organize from day one.</p>
            </div>
        </div>

        <div class="card bg-base-100 hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="bg-error/10 rounded-lg p-3 w-fit mb-4">
                    <span class="icon-[tabler--code] size-6 text-error"></span>
                </div>
                <h3 class="card-title text-lg">Embed Anywhere</h3>
                <p class="text-base-content/60">Embed forms directly into your existing website with a simple code snippet.</p>
            </div>
        </div>
    </div>

    {{-- Notify Me --}}
    <div class="card bg-base-100">
        <div class="card-body text-center py-8">
            <h3 class="text-lg font-semibold mb-2">Want to be notified when Lead Magnet is available?</h3>
            <p class="text-base-content/60 mb-4">We'll let you know as soon as this feature is ready.</p>
            <button type="button" class="btn btn-primary btn-soft" disabled>
                <span class="icon-[tabler--bell] size-5"></span>
                Notify Me
            </button>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold text-lg mb-4">In the meantime...</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('clients.create') }}?status=lead" class="flex items-center gap-3 p-4 rounded-lg border border-base-200 hover:border-warning hover:bg-warning/5 transition-colors">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--user-plus] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="font-medium">Add Lead Manually</p>
                        <p class="text-sm text-base-content/60">Create a new lead record</p>
                    </div>
                </a>
                <a href="{{ route('clients.leads') }}" class="flex items-center gap-3 p-4 rounded-lg border border-base-200 hover:border-primary hover:bg-primary/5 transition-colors">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--target] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="font-medium">View Leads</p>
                        <p class="text-sm text-base-content/60">Manage your existing leads</p>
                    </div>
                </a>
                <a href="{{ route('clients.tags.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-base-200 hover:border-success hover:bg-success/5 transition-colors">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--tags] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="font-medium">Create Tags</p>
                        <p class="text-sm text-base-content/60">Set up tags for leads</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
