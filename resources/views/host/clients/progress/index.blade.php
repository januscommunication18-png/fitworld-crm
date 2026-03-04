@extends('layouts.dashboard')

@section('title', 'Progress History - ' . $client->full_name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}"><span class="icon-[tabler--users] me-1 size-4"></span> Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.show', $client) }}">{{ $client->full_name }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Progress History</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-3">
                    <span class="icon-[tabler--chart-line] size-7 text-primary"></span>
                    Progress History
                </h1>
                <p class="text-base-content/60 mt-1">{{ $client->full_name }}</p>
            </div>
        </div>
    </div>

    @if($progressReports->isEmpty())
        {{-- Empty State --}}
        <div class="card bg-base-100">
            <div class="card-body text-center py-16">
                <span class="icon-[tabler--chart-line] size-20 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="font-semibold text-xl mb-2">No Progress Recorded</h3>
                <p class="text-base-content/60 max-w-md mx-auto">
                    No progress reports have been recorded for {{ $client->first_name }} yet.
                    Progress can be recorded from class sessions with attached progress templates.
                </p>
                <a href="{{ route('clients.show', $client) }}" class="btn btn-primary mt-6">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Profile
                </a>
            </div>
        </div>
    @else
        {{-- Template Summary Cards --}}
        @if(count($templateStats) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($templateStats as $stat)
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-primary/10 p-2 rounded-lg">
                                        <span class="icon-[tabler--{{ $stat['template']->icon ?? 'chart-line' }}] size-6 text-primary"></span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold">{{ $stat['template']->name }}</h3>
                                        <p class="text-sm text-base-content/60">{{ $stat['total_reports'] }} report(s)</p>
                                    </div>
                                </div>
                                @if($stat['trend'] === 'up')
                                    <span class="badge badge-success badge-soft gap-1">
                                        <span class="icon-[tabler--trending-up] size-4"></span>
                                        Improving
                                    </span>
                                @elseif($stat['trend'] === 'down')
                                    <span class="badge badge-error badge-soft gap-1">
                                        <span class="icon-[tabler--trending-down] size-4"></span>
                                        Declining
                                    </span>
                                @else
                                    <span class="badge badge-neutral badge-soft gap-1">
                                        <span class="icon-[tabler--minus] size-4"></span>
                                        Stable
                                    </span>
                                @endif
                            </div>
                            @if($stat['latest_score'])
                                <div class="mt-4 pt-4 border-t border-base-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-base-content/60">Latest Score</span>
                                        <span class="font-semibold text-lg">{{ number_format($stat['latest_score'], 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-base-200 rounded-full h-2 mt-2">
                                        <div class="bg-primary h-2 rounded-full" style="width: {{ min(100, $stat['latest_score']) }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Progress Timeline --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="icon-[tabler--history] size-5 me-2"></span>
                    Progress Timeline
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="divide-y divide-base-200">
                    @foreach($progressReports as $report)
                        <a href="{{ route('clients.progress.show', [$client, $report]) }}"
                           class="flex items-center gap-4 p-4 hover:bg-base-200/50 transition-colors">
                            {{-- Date --}}
                            <div class="text-center min-w-[60px]">
                                <div class="text-2xl font-bold text-primary">{{ $report->report_date->format('d') }}</div>
                                <div class="text-xs text-base-content/60 uppercase">{{ $report->report_date->format('M Y') }}</div>
                            </div>

                            {{-- Vertical Line --}}
                            <div class="relative">
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="icon-[tabler--{{ $report->template->icon ?? 'chart-line' }}] size-4 text-primary"></span>
                                    <h4 class="font-semibold">{{ $report->template->name }}</h4>
                                </div>
                                @if($report->classSession)
                                    <p class="text-sm text-base-content/60 mt-1">
                                        <span class="icon-[tabler--calendar-event] size-4 align-middle me-1"></span>
                                        {{ $report->classSession->classPlan?->name ?? 'Class Session' }}
                                    </p>
                                @endif
                                @if($report->trainer_notes)
                                    <p class="text-sm text-base-content/50 mt-1 truncate">
                                        {{ Str::limit($report->trainer_notes, 80) }}
                                    </p>
                                @endif
                            </div>

                            {{-- Score --}}
                            <div class="text-right">
                                @if($report->overall_score)
                                    <div class="text-lg font-bold {{ $report->overall_score >= 70 ? 'text-success' : ($report->overall_score >= 40 ? 'text-warning' : 'text-error') }}">
                                        {{ number_format($report->overall_score, 1) }}%
                                    </div>
                                @else
                                    <span class="badge badge-ghost badge-sm">No Score</span>
                                @endif
                                <div class="text-xs text-base-content/50 mt-1">
                                    @if($report->recordedBy)
                                        by {{ $report->recordedBy->name }}
                                    @endif
                                </div>
                            </div>

                            {{-- Arrow --}}
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
