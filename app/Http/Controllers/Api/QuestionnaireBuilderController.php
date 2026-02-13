<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionnaireBlock;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireStep;
use App\Models\QuestionnaireVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionnaireBuilderController extends Controller
{
    /**
     * Add a step to a wizard questionnaire.
     */
    public function storeStep(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'questionnaire_version_id' => 'required|exists:questionnaire_versions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $version = QuestionnaireVersion::findOrFail($request->questionnaire_version_id);

        // Verify version belongs to questionnaire
        if ($version->questionnaire_id !== $questionnaire->id) {
            return response()->json(['message' => 'Version does not belong to this questionnaire.'], 422);
        }

        $maxSortOrder = $version->steps()->max('sort_order') ?? -1;

        $step = $version->steps()->create([
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'message' => 'Step added successfully.',
            'step' => $step,
        ]);
    }

    /**
     * Update a step.
     */
    public function updateStep(Request $request, Questionnaire $questionnaire, QuestionnaireStep $step)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $step->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Step updated successfully.',
            'step' => $step,
        ]);
    }

    /**
     * Delete a step.
     */
    public function destroyStep(Questionnaire $questionnaire, QuestionnaireStep $step)
    {
        $this->authorizeHost($questionnaire);

        $step->delete();

        return response()->json([
            'message' => 'Step deleted successfully.',
        ]);
    }

    /**
     * Add a block to a questionnaire version (or step).
     */
    public function storeBlock(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'questionnaire_version_id' => 'required|exists:questionnaire_versions,id',
            'step_id' => 'nullable|exists:questionnaire_steps,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'display_style' => 'in:plain,card',
            'visibility' => 'in:public,internal',
        ]);

        $version = QuestionnaireVersion::findOrFail($request->questionnaire_version_id);

        // Verify version belongs to questionnaire
        if ($version->questionnaire_id !== $questionnaire->id) {
            return response()->json(['message' => 'Version does not belong to this questionnaire.'], 422);
        }

        // Get max sort order
        $query = $version->blocks();
        if ($request->step_id) {
            $query->where('step_id', $request->step_id);
        }
        $maxSortOrder = $query->max('sort_order') ?? -1;

        $block = $version->blocks()->create([
            'step_id' => $request->step_id,
            'title' => $request->title,
            'description' => $request->description,
            'display_style' => $request->display_style ?? 'plain',
            'visibility' => $request->visibility ?? 'public',
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'message' => 'Block added successfully.',
            'block' => $block,
        ]);
    }

    /**
     * Update a block.
     */
    public function updateBlock(Request $request, Questionnaire $questionnaire, QuestionnaireBlock $block)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'display_style' => 'in:plain,card',
            'visibility' => 'in:public,internal',
        ]);

        $block->update([
            'title' => $request->title,
            'description' => $request->description,
            'display_style' => $request->display_style ?? $block->display_style,
            'visibility' => $request->visibility ?? $block->visibility,
        ]);

        return response()->json([
            'message' => 'Block updated successfully.',
            'block' => $block,
        ]);
    }

    /**
     * Delete a block.
     */
    public function destroyBlock(Questionnaire $questionnaire, QuestionnaireBlock $block)
    {
        $this->authorizeHost($questionnaire);

        $block->delete();

        return response()->json([
            'message' => 'Block deleted successfully.',
        ]);
    }

    /**
     * Add a question to a block.
     */
    public function storeQuestion(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'questionnaire_block_id' => 'required|exists:questionnaire_blocks,id',
            'question_label' => 'required|string|max:500',
            'question_type' => 'required|in:short_text,long_text,email,phone,yes_no,single_select,multi_select,dropdown,date,number,acknowledgement',
            'options' => 'nullable|array',
            'options.*.key' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
            'is_required' => 'boolean',
            'help_text' => 'nullable|string|max:500',
            'placeholder' => 'nullable|string|max:255',
            'default_value' => 'nullable|string|max:255',
            'visibility' => 'in:client,instructor_only',
            'is_sensitive' => 'boolean',
        ]);

        $block = QuestionnaireBlock::findOrFail($request->questionnaire_block_id);

        // Verify block belongs to this questionnaire
        if ($block->version->questionnaire_id !== $questionnaire->id) {
            return response()->json(['message' => 'Block does not belong to this questionnaire.'], 422);
        }

        $maxSortOrder = $block->questions()->max('sort_order') ?? -1;

        // Generate unique question key
        $baseKey = Str::slug($request->question_label, '_');
        $key = $baseKey . '_' . Str::random(4);

        $question = $block->questions()->create([
            'question_key' => $key,
            'question_label' => $request->question_label,
            'question_type' => $request->question_type,
            'options' => $request->options,
            'is_required' => $request->boolean('is_required'),
            'help_text' => $request->help_text,
            'placeholder' => $request->placeholder,
            'default_value' => $request->default_value,
            'visibility' => $request->visibility ?? 'client',
            'is_sensitive' => $request->boolean('is_sensitive'),
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'message' => 'Question added successfully.',
            'question' => $question,
        ]);
    }

    /**
     * Update a question.
     */
    public function updateQuestion(Request $request, Questionnaire $questionnaire, QuestionnaireQuestion $question)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'question_label' => 'required|string|max:500',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'help_text' => 'nullable|string|max:500',
            'placeholder' => 'nullable|string|max:255',
            'default_value' => 'nullable|string|max:255',
            'visibility' => 'in:client,instructor_only',
            'is_sensitive' => 'boolean',
        ]);

        $question->update([
            'question_label' => $request->question_label,
            'options' => $request->options ?? $question->options,
            'is_required' => $request->boolean('is_required'),
            'help_text' => $request->help_text,
            'placeholder' => $request->placeholder,
            'default_value' => $request->default_value,
            'visibility' => $request->visibility ?? $question->visibility,
            'is_sensitive' => $request->boolean('is_sensitive'),
        ]);

        return response()->json([
            'message' => 'Question updated successfully.',
            'question' => $question,
        ]);
    }

    /**
     * Delete a question.
     */
    public function destroyQuestion(Questionnaire $questionnaire, QuestionnaireQuestion $question)
    {
        $this->authorizeHost($questionnaire);

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully.',
        ]);
    }

    /**
     * Reorder steps/blocks/questions.
     */
    public function reorder(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeHost($questionnaire);

        $request->validate([
            'type' => 'required|in:steps,blocks,questions',
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        $modelClass = match ($request->type) {
            'steps' => QuestionnaireStep::class,
            'blocks' => QuestionnaireBlock::class,
            'questions' => QuestionnaireQuestion::class,
        };

        foreach ($request->items as $item) {
            $modelClass::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'message' => ucfirst($request->type) . ' reordered successfully.',
        ]);
    }

    /**
     * Verify the questionnaire belongs to the current host.
     */
    protected function authorizeHost(Questionnaire $questionnaire): void
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        if ($questionnaire->host_id !== $host->id) {
            abort(403, 'Unauthorized access to this questionnaire.');
        }
    }
}
