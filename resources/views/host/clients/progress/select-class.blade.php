@extends('layouts.dashboard')

@section('title', 'Record Progress - ' . $client->full_name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.show', $client) }}">{{ $client->full_name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Record Progress</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div class="flex items-center gap-4 flex-1">
            <div class="avatar placeholder">
                <div class="bg-primary/10 text-primary w-12 h-12 rounded-full">
                    <span class="text-lg font-bold">{{ $client->initials }}</span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Record Progress</h1>
                <p class="text-base-content/60">{{ $client->full_name }}</p>
            </div>
        </div>
    </div>

    {{-- Today's Classes --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-2 mb-4">
                <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                <h2 class="card-title text-lg">Today's Classes</h2>
                <span class="badge badge-primary">{{ now()->format('l, M j') }}</span>
            </div>

            @if($todaysClasses->isEmpty())
                <div class="text-center py-12">
                    <span class="icon-[tabler--calendar-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="font-semibold text-lg mb-2">No Classes Today</h3>
                    <p class="text-base-content/60 mb-4">{{ $client->first_name }} is not booked for any classes today.</p>
                    <a href="{{ route('clients.show', $client) }}" class="btn btn-primary">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back to Client
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($todaysClasses as $classSession)
                        <div class="border border-base-300 rounded-xl p-5 hover:border-primary/50 hover:bg-primary/5 transition-colors">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <span class="icon-[tabler--yoga] size-7 text-primary"></span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg">{{ $classSession->classPlan?->name ?? $classSession->display_title }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-base-content/60 mt-1">
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--clock] size-4"></span>
                                            {{ $classSession->start_time->format('g:i A') }} - {{ $classSession->end_time->format('g:i A') }}
                                        </span>
                                        @if($classSession->primaryInstructor)
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--user] size-4"></span>
                                                {{ $classSession->primaryInstructor->name }}
                                            </span>
                                        @endif
                                        @if($classSession->location)
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--map-pin] size-4"></span>
                                                {{ $classSession->location->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Progress Template Options --}}
                            <div class="border-t border-base-200 pt-4">
                                <p class="text-sm text-base-content/60 mb-3">Select a progress template:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($progressTemplates as $template)
                                        <a href="{{ route('class-sessions.record-progress', [$classSession, $template]) }}?client={{ $client->id }}"
                                           class="btn btn-primary">
                                            <span class="icon-[tabler--{{ $template->icon ?? 'chart-line' }}] size-5"></span>
                                            {{ $template->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Progress Reports --}}
    @php
        $recentReports = $client->progressReports()
            ->with(['template', 'classSession.classPlan'])
            ->orderBy('report_date', 'desc')
            ->take(5)
            ->get();
    @endphp
    @if($recentReports->count() > 0)
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="icon-[tabler--history] size-5 text-primary"></span>
                    <h2 class="card-title text-lg">Recent Progress Reports</h2>
                </div>
                <a href="{{ route('clients.progress.index', $client) }}" class="btn btn-ghost btn-sm">
                    View All
                    <span class="icon-[tabler--arrow-right] size-4"></span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReports as $report)
                            <tr class="hover">
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--{{ $report->template->icon ?? 'chart-line' }}] size-4 text-primary"></span>
                                        {{ $report->template->name }}
                                    </div>
                                </td>
                                <td>{{ $report->classSession?->classPlan?->name ?? '-' }}</td>
                                <td>{{ $report->report_date->format('M j, Y') }}</td>
                                <td>
                                    @if($report->overall_score !== null)
                                        <span class="font-semibold {{ $report->overall_score >= 70 ? 'text-success' : ($report->overall_score >= 40 ? 'text-warning' : 'text-error') }}">
                                            {{ number_format($report->overall_score, 0) }}%
                                        </span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
