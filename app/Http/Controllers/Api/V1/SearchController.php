<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Question;
use App\Models\QuestionAnswer;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // 1. General Search (Text)
        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('profile_title', 'like', "%{$search}%");
            });
        }

        // 2. Category Filter
        if ($request->has('category_id') && $request->category_id != null) {
            $query->where('category_id', $request->category_id);
        }

        // 3. Dynamic Question/Answer Filters
        // Expecting 'filters' as an array: [ question_id => value, ... ]
        if ($request->has('filters') && is_array($request->filters)) {
            foreach ($request->filters as $questionId => $answerValue) {
                if ($answerValue) {
                    // Filter users who have answered this question with the specific value
                    // We use whereHas to check the relation
                    $query->whereHas('answers', function ($q) use ($questionId, $answerValue) {
                        $q->where('question_id', $questionId);

                        // Handle JSON 'answer' column. 
                        // Assuming basic equality for simple types (text, number, boolean)
                        // For multiple_choice, it might be stored as an array or string.
                        // We'll use JSON_CONTAINS or simple LIKE depending on storage.
                        // Ideally, if it's a single value stored in JSON: where('answer', $answerValue) or standard JSON querying.

                        // If answer is stored as ["Value"], we might need whereJsonContains.
                        // If it's just "Value", strictly matching might be tricky if it's cast to array in model.
                        // Let's try basic JSON containment which works for ["Value"] or "Value" in recent MySQL/MariaDB/Postgres.

                        // However, QuestionAnswer model casts 'answer' => 'array'.
                        // So in DB it is JSON.
                        // If the user sends "Yes", we search if JSON contains "Yes".

                        $q->whereJsonContains('answer', $answerValue);
                    });
                }
            }
        }

        // Only active/approved users usually
        $query->where('is_approved', true);

        // Eager load relationships needed for the response
        $query->with(['answers.question', 'category']);

        // Execute Query
        $users = $query->paginate(20);

        // Transform collection to add helper fields
        $users->getCollection()->transform(function ($user) {
            // 1. Format Image URL
            $user->image = $user->profile_photo_url; // Default fallback

            if ($user->profile_photo_path) {
                $url = url('storage/' . $user->profile_photo_path);
                $user->image = $url;
                $user->profile_photo_url = $url; // Overwrite accessor value with correct URL
            }

            // 2. Format Answers (questions_data)
            $user->questions_data = $user->answers->map(function ($answer) {
                return [
                    'question_id' => $answer->question_id,
                    'question' => $answer->question->question ?? null,
                    'type' => $answer->question->type ?? null,
                    'answer' => $answer->answer,
                ];
            });

            // 3. Format Cover Photo
            if ($user->cover_photo_path) {
                $url = url('storage/' . $user->cover_photo_path);
                $user->cover_photo = $url;
                $user->cover_photo_path = $url; // Update path to be full URL as requested
            } else {
                $user->cover_photo = null;
            }

            // 4. Category Data (Optional but good)
            $user->category_data = $user->category ? [
                'id' => $user->category->id,
                'name' => $user->category->name
            ] : null;

            return $user;
        });

        // 4. Get Filterable Questions (if category is selected)
        $filterableQuestions = [];
        if ($request->has('category_id') && $request->category_id != null) {
            $filterableQuestions = Question::where('category_id', $request->category_id)
                ->where('type', '!=', 'text') // Exclude text questions as requested
                ->select('id', 'question', 'type', 'choices')
                ->get();
        }

        return response()->json([
            'users' => $users,
            'filterable_questions' => $filterableQuestions,
        ]);
    }
}
