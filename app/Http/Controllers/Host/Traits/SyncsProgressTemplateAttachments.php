<?php

namespace App\Http\Controllers\Host\Traits;

use App\Models\HostProgressTemplate;
use App\Models\ProgressTemplate;
use App\Models\ProgressTemplateAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait SyncsProgressTemplateAttachments
{
    /**
     * Get enabled progress templates for the current host.
     */
    protected function getEnabledProgressTemplates(): Collection
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        // Check if host has the progress-templates feature enabled
        if (!$host->hasFeature('progress-templates')) {
            return collect();
        }

        // Get enabled templates for this host
        $enabledTemplateIds = HostProgressTemplate::where('host_id', $host->id)
            ->where('is_enabled', true)
            ->pluck('progress_template_id');

        return ProgressTemplate::whereIn('id', $enabledTemplateIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Sync progress template attachments for a model (ClassPlan).
     */
    protected function syncProgressTemplateAttachments(Model $model, Request $request): void
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        // Check if host has the feature enabled
        if (!$host->hasFeature('progress-templates')) {
            return;
        }

        $attachments = $request->input('progress_template_attachments', []);

        // Get IDs of templates to attach
        $attachTemplateIds = [];
        foreach ($attachments as $templateId => $data) {
            if (!empty($data['attached'])) {
                $attachTemplateIds[$templateId] = [
                    'trigger_point' => $data['trigger_point'] ?? ProgressTemplateAttachment::TRIGGER_AFTER_CLASS,
                    'tracking_frequency' => $data['tracking_frequency'] ?? ProgressTemplateAttachment::FREQUENCY_EVERY_CLASS,
                    'tracking_interval_days' => ($data['tracking_frequency'] ?? '') === 'custom'
                        ? ($data['tracking_interval_days'] ?? null)
                        : null,
                    'is_required' => !empty($data['is_required']),
                    'notify_instructor' => !empty($data['notify_instructor']) || !isset($data['notify_instructor']),
                ];
            }
        }

        // Delete attachments that are no longer selected
        $model->progressTemplateAttachments()
            ->whereNotIn('progress_template_id', array_keys($attachTemplateIds))
            ->delete();

        // Create or update attachments
        foreach ($attachTemplateIds as $templateId => $data) {
            $model->progressTemplateAttachments()->updateOrCreate(
                [
                    'host_id' => $host->id,
                    'progress_template_id' => $templateId,
                ],
                $data
            );
        }
    }
}
