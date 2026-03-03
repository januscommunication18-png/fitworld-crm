@extends('backoffice.layouts.app')

@section('title', 'Scheduled Tasks')
@section('page-title', 'Scheduled Tasks')

@section('content')
<div class="space-y-6">
    {{-- Info Banner --}}
    <div class="alert alert-info">
        <span class="icon-[tabler--clock] size-5"></span>
        <div>
            <div class="font-medium">System Scheduled Tasks</div>
            <div class="text-sm">These tasks run automatically on a schedule. Studios can enable/disable automations from their Settings &gt; Automation Rules page. You can manually run tasks here if needed.</div>
        </div>
    </div>

    {{-- Scheduled Tasks --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--clock-play] size-5"></span>
                Scheduled Tasks
            </h3>
        </div>
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Schedule</th>
                            <th>Description</th>
                            <th>Enabled Studios</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduledTasks as $task)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-primary/10 rounded-lg">
                                        <span class="icon-[tabler--{{ $task['icon'] }}] size-5 text-primary"></span>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $task['name'] }}</div>
                                        <div class="text-xs text-base-content/50 font-mono">{{ $task['command'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft badge-sm">{{ $task['schedule'] }}</span>
                            </td>
                            <td class="text-sm text-base-content/70 max-w-xs">{{ $task['description'] }}</td>
                            <td>
                                @if(isset($task['enabled_count']))
                                    <span class="badge {{ $task['enabled_count'] > 0 ? 'badge-success' : 'badge-neutral' }} badge-sm">
                                        {{ $task['enabled_count'] }} studio{{ $task['enabled_count'] !== 1 ? 's' : '' }}
                                    </span>
                                @else
                                    <span class="badge badge-info badge-sm">System</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(isset($task['can_test']) && $task['can_test'])
                                    <form action="{{ route('backoffice.settings.automation.test', $task['command']) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-soft btn-xs">
                                            <span class="icon-[tabler--test-pipe] size-4"></span>
                                            Dry Run
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('backoffice.settings.automation.run', $task['command']) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-xs"
                                            onclick="return confirm('Are you sure you want to run \'{{ $task['name'] }}\' now? This will process all eligible studios.')">
                                            <span class="icon-[tabler--player-play] size-4"></span>
                                            Run Now
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

    {{-- Studio Automation Status Overview --}}
    <div class="card bg-base-100">
        <div class="card-header">
            <h3 class="card-title">
                <span class="icon-[tabler--robot] size-5"></span>
                Automation Status by Studio
            </h3>
        </div>
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Studio</th>
                            <th class="text-center">Class Reminder</th>
                            <th class="text-center">Welcome Email</th>
                            <th class="text-center">Win-back</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studioAutomationStatus as $studio)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $studio['name'] }}</div>
                            </td>
                            <td class="text-center">
                                @if($studio['class_reminder'])
                                    <span class="badge badge-success badge-xs">On</span>
                                @else
                                    <span class="badge badge-neutral badge-xs">Off</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($studio['welcome_email'])
                                    <span class="badge badge-success badge-xs">On</span>
                                @else
                                    <span class="badge badge-neutral badge-xs">Off</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($studio['winback_campaign'])
                                    <span class="badge badge-success badge-xs">On</span>
                                @else
                                    <span class="badge badge-neutral badge-xs">Off</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-base-content/50 py-8">
                                No studios have configured automation settings yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Cron Setup Info --}}
    <div class="alert alert-warning">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <div>
            <div class="font-medium">Cron Job Required</div>
            <div class="text-sm">
                Ensure the Laravel scheduler is running via cron:
                <code class="bg-base-content/10 px-2 py-0.5 rounded ml-1">* * * * * php artisan schedule:run >> /dev/null 2>&1</code>
            </div>
        </div>
    </div>
</div>
@endsection
