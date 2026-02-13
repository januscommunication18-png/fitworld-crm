<?php

namespace App\Http\Controllers;

use App\Models\QuestionnaireResponse;
use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionnaireResponseController extends Controller
{
    /**
     * Display the questionnaire form to the client.
     */
    public function show(string $token)
    {
        $response = QuestionnaireResponse::with([
            'version.questionnaire',
            'version.steps.blocks.questions',
            'version.blocks.questions',
            'answers',
            'host',
        ])->byToken($token)->first();

        if (!$response) {
            abort(404, 'Questionnaire not found or link has expired.');
        }

        if ($response->isCompleted()) {
            return view('questionnaire.completed', [
                'response' => $response,
                'questionnaire' => $response->version->questionnaire,
            ]);
        }

        $questionnaire = $response->version->questionnaire;
        $version = $response->version;

        // Start tracking if pending
        if ($response->isPending()) {
            $response->start(request()->ip(), request()->userAgent());
        }

        // Load existing answers
        $existingAnswers = $response->answers->keyBy('question_id');

        if ($questionnaire->isWizard()) {
            return view('questionnaire.wizard', [
                'response' => $response,
                'questionnaire' => $questionnaire,
                'version' => $version,
                'existingAnswers' => $existingAnswers,
                'currentStep' => $response->current_step ?? 0,
            ]);
        }

        return view('questionnaire.single', [
            'response' => $response,
            'questionnaire' => $questionnaire,
            'version' => $version,
            'existingAnswers' => $existingAnswers,
        ]);
    }

    /**
     * Save answers for a single-page questionnaire.
     */
    public function store(Request $request, string $token)
    {
        $response = QuestionnaireResponse::with([
            'version.blocks.questions',
        ])->byToken($token)->first();

        if (!$response) {
            abort(404, 'Questionnaire not found.');
        }

        if ($response->isCompleted()) {
            return redirect()->route('questionnaire.show', $token)
                ->with('info', 'This questionnaire has already been completed.');
        }

        // Get all questions
        $allQuestions = $response->version->getAllQuestions();

        // Validate answers
        $validationErrors = $this->validateAnswers($request, $allQuestions);

        if ($validationErrors->isNotEmpty()) {
            return back()->withErrors($validationErrors)->withInput();
        }

        // Save answers
        $this->saveAnswers($response, $request, $allQuestions);

        // Mark as completed
        $response->complete();

        return redirect()->route('questionnaire.show', $token);
    }

    /**
     * Save answers for a wizard step (auto-save).
     */
    public function saveStep(Request $request, string $token)
    {
        $response = QuestionnaireResponse::with([
            'version.steps.blocks.questions',
        ])->byToken($token)->first();

        if (!$response) {
            return response()->json(['error' => 'Questionnaire not found.'], 404);
        }

        if ($response->isCompleted()) {
            return response()->json(['error' => 'Questionnaire already completed.'], 400);
        }

        $stepIndex = $request->input('step_index', 0);
        $answers = $request->input('answers', []);

        // Get the current step
        $steps = $response->version->steps;
        $currentStep = $steps->get($stepIndex);

        if (!$currentStep) {
            return response()->json(['error' => 'Invalid step.'], 400);
        }

        // Get questions for this step
        $stepQuestions = $currentStep->blocks->flatMap(function ($block) {
            return $block->questions;
        });

        // Save answers for this step
        foreach ($stepQuestions as $question) {
            $key = 'q_' . $question->id;
            if (array_key_exists($key, $answers)) {
                $value = $answers[$key];

                // Handle multi-select (array to JSON)
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $response->saveAnswer($question->id, $value);
            }
        }

        // Update progress
        $response->updateProgress($stepIndex);

        return response()->json([
            'success' => true,
            'message' => 'Progress saved.',
            'current_step' => $stepIndex,
        ]);
    }

    /**
     * Complete a wizard questionnaire.
     */
    public function completeWizard(Request $request, string $token)
    {
        $response = QuestionnaireResponse::with([
            'version.steps.blocks.questions',
            'answers',
        ])->byToken($token)->first();

        if (!$response) {
            abort(404, 'Questionnaire not found.');
        }

        if ($response->isCompleted()) {
            return redirect()->route('questionnaire.show', $token)
                ->with('info', 'This questionnaire has already been completed.');
        }

        // Get all questions from all steps
        $allQuestions = $response->version->steps->flatMap(function ($step) {
            return $step->blocks->flatMap(function ($block) {
                return $block->questions;
            });
        });

        // Validate all required questions are answered
        $validationErrors = $this->validateAnswers($request, $allQuestions, true);

        if ($validationErrors->isNotEmpty()) {
            return back()->withErrors($validationErrors)->withInput();
        }

        // Save final step answers
        $this->saveAnswers($response, $request, $allQuestions);

        // Mark as completed
        $response->complete();

        return redirect()->route('questionnaire.show', $token);
    }

    /**
     * Validate answers against question requirements.
     */
    protected function validateAnswers(Request $request, $questions, bool $checkExisting = false): \Illuminate\Support\MessageBag
    {
        $errors = new \Illuminate\Support\MessageBag();

        foreach ($questions as $question) {
            $key = 'q_' . $question->id;
            $value = $request->input($key);

            // Check required
            if ($question->is_required) {
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $errors->add($key, $question->question_label . ' is required.');
                }
            }

            // Type-specific validation
            if ($value !== null && $value !== '') {
                switch ($question->question_type) {
                    case QuestionnaireQuestion::TYPE_EMAIL:
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors->add($key, 'Please enter a valid email address.');
                        }
                        break;

                    case QuestionnaireQuestion::TYPE_NUMBER:
                        if (!is_numeric($value)) {
                            $errors->add($key, 'Please enter a valid number.');
                        }
                        break;

                    case QuestionnaireQuestion::TYPE_DATE:
                        if (!strtotime($value)) {
                            $errors->add($key, 'Please enter a valid date.');
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    /**
     * Save answers to the response.
     */
    protected function saveAnswers(QuestionnaireResponse $response, Request $request, $questions): void
    {
        foreach ($questions as $question) {
            $key = 'q_' . $question->id;
            $value = $request->input($key);

            // Skip if no value provided
            if ($value === null) {
                continue;
            }

            // Handle multi-select (array to JSON)
            if (is_array($value)) {
                $value = json_encode($value);
            }

            // Handle boolean types
            if ($question->isBooleanType()) {
                $value = $value ? '1' : '0';
            }

            $response->saveAnswer($question->id, $value);
        }
    }
}
