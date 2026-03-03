@extends('layouts.dashboard')

@section('title', $trans['nav.insights.retention'] ?? 'Retention Report')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('reports.index') }}"><span class="icon-[tabler--chart-bar] size-4"></span> {{ $trans['nav.insights'] ?? 'Insights' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--trending-up] me-1 size-4"></span> {{ $trans['nav.insights.retention'] ?? 'Retention' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $trans['nav.insights.retention'] ?? 'Retention Report' }}</h1>
            <p class="text-base-content/60 text-sm">Membership growth, churn, and recurring revenue</p>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-success">{{ $data['summary']['active'] }}</div>
                <div class="text-sm text-base-content/60">Active Members</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-primary">+{{ $data['summary']['new_30_days'] }}</div>
                <div class="text-sm text-base-content/60">New (30 days)</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-warning">{{ $data['summary']['paused'] }}</div>
                <div class="text-sm text-base-content/60">Paused</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-error">{{ $data['summary']['cancelled_30_days'] }}</div>
                <div class="text-sm text-base-content/60">Cancelled (30d)</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-info">${{ number_format($data['summary']['mrr'], 0) }}</div>
                <div class="text-sm text-base-content/60">MRR</div>
            </div>
        </div>
    </div>

    {{-- Membership Chart --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Membership Trend (Last 12 Months)</h2>
            <div id="membership-chart" style="height: 300px;"></div>
        </div>
    </div>

    {{-- Breakdown Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- By Plan --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">Members by Plan</h2>
                <div class="space-y-3">
                    @forelse($data['by_plan'] as $plan => $count)
                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                            <span>{{ $plan }}</span>
                            <span class="font-bold">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-base-content/40">No memberships</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Status Breakdown --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="text-lg font-semibold mb-4">Status Breakdown</h2>
                <div class="space-y-3">
                    @forelse($data['status_breakdown'] as $status => $count)
                        <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                @if($status === 'active')
                                    <span class="w-2 h-2 rounded-full bg-success"></span>
                                @elseif($status === 'paused')
                                    <span class="w-2 h-2 rounded-full bg-warning"></span>
                                @elseif($status === 'cancelled')
                                    <span class="w-2 h-2 rounded-full bg-error"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-base-content/30"></span>
                                @endif
                                <span class="capitalize">{{ str_replace('_', ' ', $status) }}</span>
                            </div>
                            <span class="font-bold">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-base-content/40">No data</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Growth Metrics --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Monthly Growth (Last 6 Months)</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">New</th>
                            <th class="text-center">Cancelled</th>
                            <th class="text-center">Net</th>
                            <th class="text-center">Churn Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['growth'] as $month)
                            <tr>
                                <td class="font-medium">{{ $month['month'] }}</td>
                                <td class="text-center text-success">+{{ $month['new'] }}</td>
                                <td class="text-center text-error">-{{ $month['cancelled'] }}</td>
                                <td class="text-center font-bold {{ $month['new'] - $month['cancelled'] >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ $month['new'] - $month['cancelled'] >= 0 ? '+' : '' }}{{ $month['new'] - $month['cancelled'] }}
                                </td>
                                <td class="text-center">
                                    @if($month['churn_rate'] > 5)
                                        <span class="badge badge-error badge-sm">{{ $month['churn_rate'] }}%</span>
                                    @elseif($month['churn_rate'] > 2)
                                        <span class="badge badge-warning badge-sm">{{ $month['churn_rate'] }}%</span>
                                    @else
                                        <span class="badge badge-success badge-sm">{{ $month['churn_rate'] }}%</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Key Metrics --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Key Metrics</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 bg-primary/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-base-content/70">Average Revenue Per Member (ARPM)</span>
                        <span class="icon-[tabler--currency-dollar] size-5 text-primary"></span>
                    </div>
                    <div class="text-2xl font-bold text-primary mt-2">${{ number_format($data['arpm'], 2) }}</div>
                </div>
                <div class="p-4 bg-success/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-base-content/70">Monthly Recurring Revenue</span>
                        <span class="icon-[tabler--repeat] size-5 text-success"></span>
                    </div>
                    <div class="text-2xl font-bold text-success mt-2">${{ number_format($data['summary']['mrr'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
    @vite(['resources/js/apps/dashboard.js'])
    <script id="membership-chart-data" type="application/json">@json($chartData)</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = JSON.parse(document.getElementById('membership-chart-data').textContent);

    const options = {
        series: [
            { name: 'Active Members', data: chartData.active },
            { name: 'New Members', data: chartData.new }
        ],
        chart: {
            type: 'line',
            height: 300,
            toolbar: { show: false },
            fontFamily: 'inherit',
        },
        colors: ['#3b82f6', '#10b981'],
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: chartData.labels,
            labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
        },
        yaxis: {
            labels: { style: { colors: '#9ca3af', fontSize: '11px' } }
        },
        legend: { position: 'top' },
        grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
        dataLabels: { enabled: false }
    };

    const chart = new ApexCharts(document.querySelector("#membership-chart"), options);
    chart.render();
});
</script>
@endpush
