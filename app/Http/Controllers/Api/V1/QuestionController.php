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

        if (! $categoryId) {
            return response()->json(['message' => 'category_id is required'], 400);
        }

        $questions = Question::where('category_id', $categoryId)->get();

        return response()->json(['questions' => $questions]);
    }

    /**
     * Submit answers for questions. Requires authentication.
     * Expected payload: { answers: [ { question_id: x, answer: '...' }, ... ] }
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $created = [];
        foreach ($request->input('answers') as $item) {
            $answerValue = $item['answer'] ?? null;
            // normalize to JSON-storable value
            if (is_array($answerValue)) {
                $stored = $answerValue;
            } else {
                $stored = [ 'value' => $answerValue ];
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
