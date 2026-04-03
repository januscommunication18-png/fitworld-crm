@extends('layouts.subdomain')

@section('title', 'Select Your Schedule — ' . $host->studio_name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-base-200 via-base-100 to-base-200">
    {{-- Header --}}
    <nav class="bg-base-100/80 backdrop-blur-sm border-b border-base-200 sticky top-0 z-50" style="height: 70px;">
        <div class="container-fixed h-full">
            <div class="flex items-center justify-between h-full">
                @if($host->logo_url)
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center">
                        <img src="{{ $host->logo_url }}" alt="{{ $host->studio_name }}" class="h-10 w-auto max-w-[160px] object-contain">
                    </a>
                @else
                    <a href="{{ route('subdomain.home', ['subdomain' => $host->subdomain]) }}" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-base font-bold text-primary-content">{{ strtoupper(substr($host->studio_name, 0, 1)) }}</span>
                        </div>
                        <span class="font-bold text-base hidden sm:inline">{{ $host->studio_name }}</span>
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <div class="container-fixed py-10">
        <div class="max-w-2xl mx-auto">
            {{-- Welcome --}}
            <div class="text-center mb-8">
                <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--check-circle] size-10 text-success"></span>
                </div>
                <h1 class="text-3xl font-bold">Welcome, {{ $client->first_name }}!</h1>
                <p class="text-base-content/60 mt-2 text-lg">Thanks for registering for the membership.</p>
            </div>

            {{-- Membership Info Card --}}
            <div class="card bg-base-100 shadow-lg border border-base-200 mb-6">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-success/10 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--id-badge-2] size-7 text-success"></span>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold">{{ $membershipPlan->name }}</h2>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60 mt-1">
                                <span class="badge badge-success badge-sm">Active</span>
                                @if($membershipPlan->type === 'unlimited')
                                    <span class="flex items-center gap-1"><span class="icon-[tabler--infinity] size-4"></span> Unlimited Classes</span>
                                @else
                                    <span class="flex items-center gap-1"><span class="icon-[tabler--ticket] size-4"></span> {{ $membership->credits_remaining ?? $membershipPlan->credits_per_cycle }} credits</span>
                                @endif
                                @if($membership->expires_at)
                                    <span class="flex items-center gap-1"><span class="icon-[tabler--calendar] size-4"></span> Expires {{ $membership->expires_at->format('M j, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success mb-6">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Schedule Selection --}}
            @if($schedules->isNotEmpty())
                <div class="card bg-base-100 shadow-lg border border-base-200">
                    <div class="card-body">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="icon-[tabler--calendar-repeat] size-5 text-primary"></span>
                            <h3 class="text-lg font-bold">Select Your Schedule</h3>
                        </div>
                        <p class="text-sm text-base-content/60 mb-4">Choose which schedules you'd like to attend. You'll be automatically enrolled into all upcoming sessions.</p>

                        <form action="{{ route('subdomain.membership-select-schedules', ['subdomain' => $host->subdomain, 'accessToken' => $token]) }}" method="POST">
                            @csrf

                            <div class="space-y-3">
                                @foreach($schedules as $schedule)
                                <label class="flex items-center gap-4 p-4 border border-base-200 rounded-xl cursor-pointer hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                    <input type="checkbox" name="schedule_ids[]" value="{{ $schedule->id }}"
                                           class="checkbox checkbox-primary">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            @if($schedule->is_recurring)
                                                <span class="icon-[tabler--calendar-repeat] size-4 text-primary"></span>
                                            @else
                                                <span class="icon-[tabler--calendar-event] size-4 text-base-content/40"></span>
                                            @endif
                                            <span class="font-semibold">{{ $schedule->title }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-base-content/60">
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--clock] size-3.5"></span>
                                                @foreach(explode(', ', $schedule->days) as $day)
                                                    <span class="badge badge-ghost badge-xs">{{ $day }}</span>
                                                @endforeach
                                                {{ $schedule->time }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--user] size-3.5"></span>
                                                {{ $schedule->instructor }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--map-pin] size-3.5"></span>
                                                {{ $schedule->location }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge badge-soft badge-sm {{ $schedule->session_count > 0 ? 'badge-success' : 'badge-neutral' }} shrink-0">
                                        {{ $schedule->session_count }} sessions
                                    </span>
                                </label>
                                @endforeach
                            </div>

                            <div class="mt-6">
                                <button type="submit" class="btn btn-primary w-full btn-lg">
                                    <span class="icon-[tabler--check] size-5"></span>
                                    Confirm My Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card bg-base-100 shadow-lg border border-base-200">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--calendar-check] size-12 text-success/30 mx-auto"></span>
                        <h3 class="text-lg font-semibold mt-4">You're all set!</h3>
                        <p class="text-base-content/60 mt-2">No schedules to select at this time. We'll notify you when sessions become available.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
