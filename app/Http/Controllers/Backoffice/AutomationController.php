<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AutomationSetting;
use App\Models\Host;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AutomationController extends Controller
{
    /**
     * Display the scheduled tasks page.
     */
    public function index()
    {
        // Get scheduled tasks with enabled counts
        $scheduledTasks = collect(AutomationSetting::getScheduledTasks())->map(function ($task) {
            // Map commands to automation keys to count enabled studios
            $automationKey = match($task['command']) {
                'automation:class-reminders' => AutomationSetting::KEY_CLASS_REMINDER,
                'automation:winback' => AutomationSetting::KEY_WINBACK_CAMPAIGN,
                default => null,
            };

            if ($automationKey) {
                $task['enabled_count'] = count(AutomationSetting::getEnabledHostIds($automationKey));
                $task['can_test'] = true;
            }

            return $task;
        });

        // Get studio automation status overview
        $studioAutomationStatus = Host::whereNotNull('studio_name')
            ->orderBy('studio_name')
            ->get()
            ->map(function ($host) {
                $settings = AutomationSetting::getSettingsForHost($host->id);

                return [
                    'name' => $host->studio_name,
                    'class_reminder' => $settings[AutomationSetting::KEY_CLASS_REMINDER]?->is_enabled ?? false,
                    'welcome_email' => $settings[AutomationSetting::KEY_WELCOME_EMAIL]?->is_enabled ?? false,
                    'winback_campaign' => $settings[AutomationSetting::KEY_WINBACK_CAMPAIGN]?->is_enabled ?? false,
                ];
            });

        return view('backoffice.settings.automation', compact('scheduledTasks', 'studioAutomationStatus'));
    }

    /**
     * Test run an automation command (dry run).
     */
    public function test(Request $request, string $command)
    {
        $allowedCommands = [
            'automation:class-reminders',
            'automation:winback',
        ];

        if (!in_array($command, $allowedCommands)) {
            return redirect()->back()
                ->with('error', 'Invalid command or test not available for this task.');
        }

        try {
            Artisan::call($command, ['--dry-run' => true]);
            $output = Artisan::output();

            return redirect()->back()
                ->with('success', "Dry run completed successfully.\n{$output}");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Test failed: ' . $e->getMessage());
        }
    }

    /**
     * Run an automation command manually.
     */
    public function run(Request $request, string $command)
    {
        $allowedCommands = [
            'automation:class-reminders',
            'automation:winback',
            'audit:archive',
            'clients:detect-at-risk',
        ];

        if (!in_array($command, $allowedCommands)) {
            return redirect()->back()
                ->with('error', 'Invalid command.');
        }

        try {
            Artisan::call($command);
            $output = Artisan::output();

            return redirect()->back()
                ->with('success', "Task '{$command}' ran successfully.\n{$output}");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Task failed: ' . $e->getMessage());
        }
    }
}
