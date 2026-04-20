<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Traits\FormatsProfileData;

class SearchController extends Controller
{
    use FormatsProfileData;
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
                if ($answerValue !== null && $answerValue !== '') {
                    // Filter users who have answered this question with the specific value
                    // We use whereHas to check the relation
                    $query->whereHas('answers', function ($q) use ($questionId, $answerValue) {
                        $q->where('question_id', $questionId);

                        // Data is stored as {"value": "..."} or {"value": ["...", "..."]}
                        // To be robust, we check:
                        // 1. Direct match (answer = "val")
                        // 2. JSON array contains (answer contains "val")
                        // 3. Legacy nested format (answer->value = "val")
                        // 4. Legacy nested JSON array (answer->value contains "val")
                        $q->where(function ($subQ) use ($answerValue) {
                            $values = is_array($answerValue) ? $answerValue : [$answerValue];
                            
                            foreach ($values as $val) {
                                $subQ->orWhere('answer', $val)
                                     ->orWhereJsonContains('answer', $val)
                                     ->orWhere('answer->value', $val)
                                     ->orWhereJsonContains('answer->value', $val);
                            }
                        });
                    });
                }
            }
        }

        // Only active/approved users usually, and EXCLUDE ADMINS
        $query->where('is_approved', true)
            ->where('is_admin', '!=', true);

        // Filter out users in "Club" category who don't have an owned club
        $query->where(function ($q) {
            $q->whereDoesntHave('category', function ($catQ) {
                $catQ->whereIn('name_en', ['Club'])
                     ->orWhereIn('name_ar', ['نادي']);
            })
            ->orWhereHas('ownedClub');
        });

        // Eager load relationships needed for the response
        $query->with(['answers.question', 'category', 'ownedClub', 'club', 'subscription']);

        // Execute Query
        $users = $query->paginate(20);

        // Transform collection to add helper fields
        $currentUser = $request->user('sanctum');
        $users->getCollection()->transform(function ($user) use ($currentUser) {
            // Standardize profile formatting and flatten it into the user object
            $profileData = $this->getProfileData($user, false, $currentUser);
            foreach ($profileData as $key => $value) {
                $user->{$key} = $value;
            }
            
            // Note: We avoid adding properties directly to $user that might shadow relationships (like subscription)
            // as this was causing 500 errors in models like User::isSubscribed().
            
            return $user;
        });

        // 4. Get Filterable Questions (if category is selected)
        $filterableQuestions = [];
        if ($request->has('category_id') && $request->category_id != null) {
            $filterableQuestions = Question::where('category_id', $request->category_id)
                ->where('type', '!=', 'text') // Exclude text questions as requested
                ->get()
                ->map(function ($q) {
                    $qData = $q->question;
                    $questionEn = is_array($qData) ? ($qData['en'] ?? null) : $qData;
                    $questionAr = is_array($qData) ? ($qData['ar'] ?? null) : $qData;

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
                        'id' => $q->id,
                        'question' => $questionEn ?: ($questionAr ?: ''),
                        'question_en' => $questionEn,
                        'question_ar' => $questionAr,
                        'type' => $q->type,
                        'choices' => $choices,
                        'choices_en' => $choicesEn,
                        'choices_ar' => $choicesAr,
                    ];
                });
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
