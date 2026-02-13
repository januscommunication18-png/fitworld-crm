<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\QuestionnaireVersion;
use App\Models\QuestionnaireStep;
use App\Models\QuestionnaireBlock;
use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuestionnaireBuilderController extends Controller
{
    /**
     * Store a new step
     */
    public function storeStep(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $validated = $request->validate([
            'questionnaire_version_id' => 'required|exists:questionnaire_versions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Get the version and check it belongs to the questionnaire
        $version = QuestionnaireVersion::where('id', $validated['questionnaire_version_id'])
            ->where('questionnaire_id', $questionnaire->id)
            ->firstOrFail();

        // Get the next sort order
        $maxOrder = QuestionnaireStep::where('questionnaire_version_id', $version->id)->max('sort_order') ?? 0;

        $step = QuestionnaireStep::create([
            'questionnaire_version_id' => $version->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'step' => $step,
        ]);
    }

    /**
     * Update a step
     */
    public function updateStep(Request $request, Questionnaire $questionnaire, QuestionnaireStep $step): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $step->update($validated);

        return response()->json([
            'success' => true,
            'step' => $step,
        ]);
    }

    /**
     * Delete a step
     */
    public function destroyStep(Questionnaire $questionnaire, QuestionnaireStep $step): JsonResponse
    {
        $step->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Store a new block
     */
    public function storeBlock(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $validated = $request->validate([
            'questionnaire_version_id' => 'required|exists:questionnaire_versions,id',
            'step_id' => 'nullable|exists:questionnaire_steps,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_style' => 'nullable|in:plain,card',
        ]);

        // Get the version and check it belongs to the questionnaire
        $version = QuestionnaireVersion::where('id', $validated['questionnaire_version_id'])
            ->where('questionnaire_id', $questionnaire->id)
            ->firstOrFail();

        // Get the next sort order
        $maxOrder = QuestionnaireBlock::where('questionnaire_version_id', $version->id)->max('sort_order') ?? 0;

        $block = QuestionnaireBlock::create([
            'questionnaire_version_id' => $version->id,
            'step_id' => $validated['step_id'] ?: null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'display_style' => $validated['display_style'] ?? 'plain',
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'block' => $block,
        ]);
    }

    /**
     * Update a block
     */
    public function updateBlock(Request $request, Questionnaire $questionnaire, QuestionnaireBlock $block): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_style' => 'nullable|in:plain,card',
        ]);

        $block->update($validated);

        return response()->json([
            'success' => true,
            'block' => $block,
        ]);
    }

    /**
     * Delete a block
     */
    public function destroyBlock(Questionnaire $questionnaire, QuestionnaireBlock $block): JsonResponse
    {
        $block->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Store a new question
     */
    public function storeQuestion(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $validated = $request->validate([
            'questionnaire_block_id' => 'required|exists:questionnaire_blocks,id',
            'question_label' => 'required|string|max:500',
            'question_type' => 'required|string|in:' . implode(',', array_keys(QuestionnaireQuestion::getQuestionTypes())),
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'is_required' => 'boolean',
            'is_sensitive' => 'boolean',
            'visibility' => 'nullable|in:client,instructor_only',
            'options' => 'nullable|array',
            'options.*.key' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
        ]);

        // Get the next sort order for this block
        $maxOrder = QuestionnaireQuestion::where('questionnaire_block_id', $validated['questionnaire_block_id'])->max('sort_order') ?? 0;

        // Generate a field key
        $fieldKey = \Illuminate\Support\Str::slug($validated['question_label'], '_');
        $fieldKey = substr($fieldKey, 0, 50);

        // Ensure uniqueness within the questionnaire
        $block = QuestionnaireBlock::find($validated['questionnaire_block_id']);
        $existingKeys = QuestionnaireQuestion::whereHas('block', function ($q) use ($block) {
            $q->where('questionnaire_version_id', $block->questionnaire_version_id);
        })->pluck('field_key')->toArray();

        $originalKey = $fieldKey;
        $counter = 1;
        while (in_array($fieldKey, $existingKeys)) {
            $fieldKey = $originalKey . '_' . $counter;
            $counter++;
        }

        $question = QuestionnaireQuestion::create([
            'questionnaire_block_id' => $validated['questionnaire_block_id'],
            'field_key' => $fieldKey,
            'question_label' => $validated['question_label'],
            'question_type' => $validated['question_type'],
            'placeholder' => $validated['placeholder'],
            'help_text' => $validated['help_text'],
            'is_required' => $validated['is_required'] ?? false,
            'is_sensitive' => $validated['is_sensitive'] ?? false,
            'visibility' => $validated['visibility'] ?? 'client',
            'options' => $validated['options'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'question' => $question,
        ]);
    }

    /**
     * Update a question
     */
    public function updateQuestion(Request $request, Questionnaire $questionnaire, QuestionnaireQuestion $question): JsonResponse
    {
        $validated = $request->validate([
            'question_label' => 'required|string|max:500',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500',
            'is_required' => 'boolean',
            'is_sensitive' => 'boolean',
            'visibility' => 'nullable|in:client,instructor_only',
            'options' => 'nullable|array',
        ]);

        $question->update($validated);

        return response()->json([
            'success' => true,
            'question' => $question,
        ]);
    }

    /**
     * Delete a question
     */
    public function destroyQuestion(Questionnaire $questionnaire, QuestionnaireQuestion $question): JsonResponse
    {
        $question->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Reorder items
     */
    public function reorder(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:steps,blocks,questions',
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.sort_order' => 'required|integer',
        ]);

        $modelClass = match ($validated['type']) {
            'steps' => QuestionnaireStep::class,
            'blocks' => QuestionnaireBlock::class,
            'questions' => QuestionnaireQuestion::class,
        };

        foreach ($validated['items'] as $item) {
            $modelClass::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
