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

        // 2. Category Filter (Legacy direct category_id)
        if ($request->has('category_id') && $request->category_id != null) {
            $query->where('category_id', $request->category_id);
        }

        // 2.1 Parent Category Filter (NEW) - Get all users in subcategories of this parent
        if ($request->has('parent_category_id') && $request->parent_category_id != null) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('parent_category_id', $request->parent_category_id);
            });
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

        // Only active/approved users usually, and EXCLUDE ADMINS
        $query->where('is_approved', true)
            ->where('is_admin', '!=', true);

        // Eager load relationships needed for the response
        $query->with(['answers.question', 'category', 'ownedClub', 'club']);

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
                $q = $answer->question;
                if (!$q)
                    return null;

                // Logic to extract en/ar question text
                $qRaw = $q->getAttributes()['question'] ?? '';
                $qData = $q->question;

                $questionEn = null;
                $questionAr = null;
                $mainQuestion = '';

                if (is_array($qData)) {
                    $questionEn = $qData['en'] ?? null;
                    $questionAr = $qData['ar'] ?? null;
                    $mainQuestion = !empty($questionEn) ? $questionEn : ($questionAr ?? '');
                } else {
                    $mainQuestion = (string) $qRaw;
                    $questionEn = $mainQuestion;
                    $questionAr = $mainQuestion;
                }

                // Logic to extract choices
                $choicesData = $q->choices;
                $choices = [];
                $choicesEn = [];
                $choicesAr = [];

                if (is_array($choicesData) && !empty($choicesData)) {
                    $choices = array_values($choicesData);
                    $choicesEn = array_keys($choicesData);
                    $choicesAr = array_values($choicesData);
                }

                return [
                    'question_id' => $answer->question_id,
                    'question' => $mainQuestion,
                    'question_en' => $questionEn,
                    'question_ar' => $questionAr,
                    'type' => $q->type ?? null,
                    'choices' => $choices,
                    'choices_en' => $choicesEn,
                    'choices_ar' => $choicesAr,
                    'answer' => $answer->answer,
                ];
            })->filter();

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
                'name' => $user->category->name,
                'is_service_provider' => $user->category->is_service_provider,
                'parent_category_id' => $user->category->parent_category_id,
                'parent_category' => $user->category->parentCategory ? [
                    'id' => $user->category->parentCategory->id,
                    'name' => $user->category->parentCategory->name,
                    'image' => $user->category->parentCategory->image ? url('storage/' . $user->category->parentCategory->image) : null,
                ] : null,
            ] : null;

            // 5. Professional & Club Data (Consistency with Profile API)
            $user->professional = [
                'club' => $user->club ? [
                    'id' => $user->club->id,
                    'name' => $user->club->name,
                    'logo' => $user->club->logo_url,
                    'user_id' => $user->club->user_id, // Club Owner User ID
                ] : null,
                'team_id' => $user->team_id,
                'position' => $user->position,
                'number' => $user->number,
                'nationality' => $user->nationality,
                'stats' => $user->stats,
            ];

            $user->is_club_account = (bool) $user->ownedClub;

            if ($user->is_club_account && $user->ownedClub) {
                $club = $user->ownedClub;
                $user->club_details = [
                    'id' => $club->id,
                    'name' => $club->name,
                    'logo' => $club->logo_url,
                    'banner' => $club->banner_url,
                ];
            }

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

        // 5. Club Search (NEW)
        $clubs = collect();
        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $clubs = \App\Models\Club::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            })
                ->with('sports')
                ->latest()
                ->get()
                ->map(function ($club) {
                    // Ensure full URLs for logo and banner if they are relative paths
                    // The model has accessors, but explicitly ensuring here for search context if needed.
                    return $club;
                });
        }

        return response()->json([
            'users' => $users,
            'clubs' => $clubs,
            'filterable_questions' => $filterableQuestions,
        ]);
    }
}
