<?php

namespace App\Http\Controllers\Host\Traits;

use App\Models\Questionnaire;
use App\Models\QuestionnaireAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait SyncsQuestionnaireAttachments
{
    /**
     * Get published questionnaires for the current host.
     */
    protected function getPublishedQuestionnaires(): Collection
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        return $host->questionnaires()
            ->where('status', Questionnaire::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    /**
     * Sync questionnaire attachments for a model.
     */
    protected function syncQuestionnaireAttachments(Model $model, Request $request): void
    {
        $attachments = $request->input('questionnaire_attachments', []);

        // Get IDs of questionnaires to attach
        $attachQuestionnaireIds = [];
        foreach ($attachments as $questionnaireId => $data) {
            if (!empty($data['attached'])) {
                $attachQuestionnaireIds[$questionnaireId] = [
                    'collection_timing' => $data['collection_timing'] ?? QuestionnaireAttachment::TIMING_AFTER_BOOKING,
                    'applies_to' => $data['applies_to'] ?? QuestionnaireAttachment::APPLIES_FIRST_TIME_ONLY,
                    'is_required' => !empty($data['is_required']),
                ];
            }
        }

        // Delete attachments that are no longer selected
        $model->questionnaireAttachments()
            ->whereNotIn('questionnaire_id', array_keys($attachQuestionnaireIds))
            ->delete();

        // Create or update attachments
        foreach ($attachQuestionnaireIds as $questionnaireId => $data) {
            $model->questionnaireAttachments()->updateOrCreate(
                ['questionnaire_id' => $questionnaireId],
                $data
            );
        }
    }
}
