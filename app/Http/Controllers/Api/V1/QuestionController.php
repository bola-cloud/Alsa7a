<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionAnswer;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Return questions for a category.
     * Accepts category id either as route param or query parameter `category_id`.
     */
    public function index(Request $request, $categoryId = null)
    {
        $categoryId = $categoryId ?? $request->query('category_id');

        if (!$categoryId) {
            return response()->json(['message' => 'category_id is required'], 400);
        }

        $questions = Question::where('category_id', $categoryId)->get();

        $data = $questions->map(function ($q) {
            // Handle question translation
            // The model casts 'question' to array. If data is legacy string, it might be null or weird.
            // But let's assume valid JSON or fallback.
            $qRaw = $q->getAttributes()['question'] ?? ''; // Get raw for safety check
            $qData = $q->question; // Casted value

            $questionEn = null;
            $questionAr = null;
            $mainQuestion = '';

            if (is_array($qData)) {
                $questionEn = $qData['en'] ?? null;
                $questionAr = $qData['ar'] ?? null;
                // Prefer English for the main string, fallback to Arabic
                $mainQuestion = !empty($questionEn) ? $questionEn : ($questionAr ?? '');
            } else {
                // Determine if raw is simpler string
                $mainQuestion = (string) $qRaw;
                $questionEn = $mainQuestion;
                $questionAr = $mainQuestion;
            }

            // Handle Choices
            // Model casts 'choices' to array.
            $choicesData = $q->choices;
            $choices = [];
            $choicesEn = [];
            $choicesAr = [];

            if (is_array($choicesData) && !empty($choicesData)) {
                // User Example: choices: ["ذكر", "أنثى"] -> Values
                // choices_en: ["Male", "Female"] -> Keys? (Assumed)
                // choices_ar: ["ذكر", "أنثى"] -> Values

                $choices = array_values($choicesData);
                $choicesEn = array_keys($choicesData); // Assuming keys are English/Codes
                $choicesAr = array_values($choicesData); // Assuming values are Arabic/Labels
            }

            return [
                'id' => $q->id,
                'category_id' => $q->category_id,
                'type' => $q->type,
                'question' => $mainQuestion,
                'question_en' => $questionEn,
                'question_ar' => $questionAr,
                'choices' => $choices,
                'choices_en' => $choicesEn,
                'choices_ar' => $choicesAr,
                'created_at' => $q->created_at,
                'updated_at' => $q->updated_at,
            ];
        });

        return response()->json(['questions' => $data]);
    }

    /**
     * Submit answers for questions. Requires authentication.
     * Expected payload: { answers: [ { question_id: x, answer: '...' }, ... ] }
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:categories,id',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $categoryId = $request->input('category_id');

        // If user is authenticated, set their category_id to the provided one
        if ($user) {
            $user->category_id = $categoryId;
            $user->save();
        }

        $created = [];
        foreach ($request->input('answers') as $item) {
            $question = Question::find($item['question_id']);
            // ensure the question belongs to the category provided
            if (!$question || $question->category_id != $categoryId) {
                return response()->json([
                    'message' => 'Question id ' . ($item['question_id'] ?? 'null') . ' does not belong to category ' . $categoryId
                ], 422);
            }

            $answerValue = $item['answer'] ?? null;
            // normalize to JSON-storable value
            if (is_array($answerValue)) {
                $stored = $answerValue;
            } else {
                $stored = ['value' => $answerValue];
            }

            $qa = QuestionAnswer::create([
                'user_id' => $user ? $user->id : null,
                'question_id' => $item['question_id'],
                'answer' => $stored,
            ]);

            $created[] = $qa;
        }

        return response()->json(['saved' => $created], 201);
    }
}
