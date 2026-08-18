<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\MarketRequest;
use App\Models\MarketApplication;
use App\Models\MarketApplicationAnswer;
use App\Models\MarketRequestQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MarketController extends Controller
{
    /**
     * Get Marketplace requests. Public.
     *
     * Country filtering is automatic — MarketRequest carries HasCountryScope,
     * so CountryScope applies the same Country-Id/user-country logic here as
     * every other country-scoped model, no manual code needed.
     *
     * Pass `user_id` to get one user's own postings — the same pattern the
     * mobile app already uses for a provider's services on their profile
     * (GET /services?provider_id=X), so a user's job listings can be shown
     * on their profile screen the same way.
     */
    public function index(Request $request)
    {
        $query = MarketRequest::with(['user', 'club', 'category', 'questions'])
            ->withCount('applications')
            ->where('status', 'active');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $requests = $query->latest()->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Market requests retrieved successfully.'
        ]);
    }

    /**
     * Get a single Marketplace request. Public, same as index — used for
     * direct links / opening a job from a notification.
     */
    public function show(Request $request, $id)
    {
        // Direct access by id - country filter must not hide it.
        // questions are loaded so the app can render the apply form directly
        // from this response.
        $marketRequest = MarketRequest::directAccess()->with(['user', 'club', 'category', 'questions'])
            ->withCount('applications')
            ->find($id);

        if (!$marketRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Market request not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $marketRequest,
            'message' => 'Market request retrieved successfully.'
        ]);
    }

    /**
     * Create a new Market Request (Job Post).
     *
     * Authorization mirrors how service providers are meant to work: any
     * user whose category has the "سوق التعاقدات (Marketplace)" checkbox
     * enabled in the admin panel (categories.is_marketplace) can post,
     * whether or not they own a club. club_id is attached only as optional
     * context when the poster happens to own one.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->category || !$user->category->is_marketplace) {
            return response()->json([
                'status' => false,
                'message' => 'Your category is not allowed to post marketplace job requests.'
            ], 403);
        }

        // Same gate a service goes through (ServiceController@store): posting a
        // request is the same kind of offer to other users, so the category
        // flag and the app-wide setting apply to it too.
        $isMandatory = (bool) $user->category->mandatory_service_verification;

        if ($isMandatory || setting('mandatory_service_verification', false)) {
            if ($user->verification_status !== 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'You must verify your profile before creating a service.'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',

            // Optional details, shown to applicants so they know where the
            // request is, when it happens and what it would cost them.
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            'address' => 'nullable|string|max:255',
            'scheduled_at' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',

            // Optional multiple-choice questions the applicant answers while
            // applying. Every question must ship its own list of choices —
            // there is no free-text question type.
            'questions' => 'nullable|array|max:20',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.is_required' => 'nullable|boolean',
            'questions.*.options' => 'required|array|min:2|max:20',
            'questions.*.options.*' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // One transaction: a request must never end up half-created with
        // some of its questions missing.
        $marketRequest = DB::transaction(function () use ($request, $user) {
            $marketRequest = MarketRequest::create([
                'user_id' => $user->id,
                'club_id' => optional($user->ownedClub)->id,
                'category_id' => $request->category_id,
                'country_id' => $user->country_id,
                'title' => $request->title,
                'description' => $request->description,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'scheduled_at' => $request->scheduled_at,
                'cost' => $request->cost,
                'status' => 'active',
            ]);

            foreach ((array) $request->input('questions', []) as $index => $question) {
                $options = array_values(array_filter(
                    (array) ($question['options'] ?? []),
                    fn ($option) => trim((string) $option) !== ''
                ));

                $marketRequest->questions()->create([
                    'question' => $question['question'],
                    'is_required' => filter_var($question['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'options' => $options,
                    'sort_order' => $index,
                ]);
            }

            return $marketRequest;
        });

        return response()->json([
            'status' => true,
            'data' => $marketRequest->load(['user', 'club', 'category', 'questions']),
            'message' => 'Market request created successfully.'
        ], 201);
    }

    /**
     * End a Market Request's visibility. Only the poster can close it, and
     * only they can reopen it — there is no separate reopen endpoint by
     * design, matching "ينهي صلاحية عرض الوظيفة في أي وقت" (a one-way action
     * from the poster's point of view; post a new listing to relist).
     */
    public function close(Request $request, $id)
    {
        $user = $request->user();
        $marketRequest = MarketRequest::find($id);

        if (!$marketRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Market request not found.'
            ], 404);
        }

        if ($marketRequest->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'You can only close your own market requests.'
            ], 403);
        }

        // Idempotent: closing an already-closed request just confirms it.
        if ($marketRequest->status !== 'closed') {
            $marketRequest->update(['status' => 'closed']);
        }

        return response()->json([
            'status' => true,
            'data' => $marketRequest,
            'message' => 'Market request closed successfully.'
        ]);
    }

    /**
     * Apply to a Market Request
     */
    public function apply(Request $request, $id)
    {
        $user = $request->user();

        $marketRequest = MarketRequest::find($id);
        if (!$marketRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Market request not found.'
            ], 404);
        }

        if ($marketRequest->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'This job posting is closed and no longer accepting applications.'
            ], 400);
        }

        if ($marketRequest->user_id === $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot apply to your own job posting.'
            ], 400);
        }

        // Prevent double applications
        $existing = MarketApplication::where('market_request_id', $marketRequest->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'You have already applied to this request.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'answers' => 'nullable|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Answers are checked against this request's own questions: required
        // ones must be answered, and a question with choices only accepts one
        // of those choices.
        $questions = $marketRequest->questions()->get()->keyBy('id');
        $submitted = [];

        foreach ((array) $request->input('answers', []) as $answer) {
            $question = $questions->get($answer['question_id'] ?? null);

            if (! $question) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => ['answers' => ['Question ' . ($answer['question_id'] ?? '?') . ' does not belong to this request.']],
                ], 422);
            }

            $value = trim((string) ($answer['answer'] ?? ''));

            // Every question is multiple choice, so an answer is only ever
            // valid if it is one of that question's own options.
            if ($value !== '' && ! in_array($value, (array) $question->options, true)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => ['answers' => ['"' . $value . '" is not one of the choices for: ' . $question->question]],
                ], 422);
            }

            if ($value !== '') {
                $submitted[$question->id] = $value;
            }
        }

        $missing = $questions->filter(fn ($question) => $question->is_required && ! isset($submitted[$question->id]));

        if ($missing->isNotEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => ['answers' => $missing->map(fn ($q) => 'Required question not answered: ' . $q->question)->values()],
            ], 422);
        }

        $cvPath = null;
        if ($request->hasFile('cv_file')) {
            $cvPath = $request->file('cv_file')->store('cvs', 'public');
        }

        $application = DB::transaction(function () use ($marketRequest, $user, $request, $cvPath, $submitted) {
            $application = MarketApplication::create([
                'market_request_id' => $marketRequest->id,
                'user_id' => $user->id,
                'notes' => $request->notes,
                'cv_path' => $cvPath,
            ]);

            foreach ($submitted as $questionId => $value) {
                $application->answers()->create([
                    'market_request_question_id' => $questionId,
                    'answer' => $value,
                ]);
            }

            return $application;
        });

        return response()->json([
            'status' => true,
            'data' => $application->load('answers.question'),
            'message' => 'Application submitted successfully. The publisher will contact you via chat.'
        ], 201);
    }

    /**
     * Get the applicants for one of my own Market Requests, paginated.
     *
     * Kept separate from myRequests() on purpose: a popular job posting can
     * attract far more applicants than fit comfortably in a list-of-jobs
     * response, so applicants are fetched per-job, on demand.
     */
    public function applications(Request $request, $id)
    {
        $user = $request->user();
        $marketRequest = MarketRequest::find($id);

        if (!$marketRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Market request not found.'
            ], 404);
        }

        if ($marketRequest->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'You can only view applicants for your own market requests.'
            ], 403);
        }

        $applications = $marketRequest->applications()
            ->with([
                // Everything getProfileData() needs, eager loaded once for the
                // whole page instead of per applicant.
                'user.category.parentCategory',
                'user.club',
                'user.ownedClub',
                'user.subscription',
                'user.answers.question',
                'user.ratingsReceived',
                'answers.question',
            ])
            ->withCount('answers')
            ->latest()
            ->paginate(15);

        // Full profile per applicant so the publisher can judge everyone from
        // this one screen — no opening a profile page per person.
        $applications->getCollection()->transform(function ($application) {
            $application->setAttribute(
                'applicant_profile',
                $application->user ? $this->applicantProfile($application->user) : null
            );

            $application->setAttribute('answers_list', $application->answers->map(function ($answer) {
                return [
                    'question_id' => $answer->market_request_question_id,
                    'question' => optional($answer->question)->question,
                    'is_required' => (bool) optional($answer->question)->is_required,
                    'answer' => $answer->answer,
                ];
            })->values());

            $application->setAttribute(
                'cv_url',
                $application->cv_path ? url('storage/' . $application->cv_path) : null
            );

            return $application;
        });

        return response()->json([
            'status' => true,
            'data' => $applications,
            'message' => 'Applicants retrieved successfully.'
        ]);
    }

    /**
     * Get Market Requests I posted myself.
     *
     * Any user can have posted requests, not just club owners, so this no
     * longer 403s for users without a club — it simply returns their own
     * list (empty if they have not posted anything). Applicant lists are
     * fetched separately per job via applications() to keep this response
     * light regardless of how many people applied.
     */
    public function myRequests(Request $request)
    {
        $user = $request->user();

        $requests = MarketRequest::with(['category', 'club', 'questions'])
            ->withCount('applications')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Your market requests retrieved successfully.'
        ]);
    }

    /**
     * Everything a publisher needs to judge an applicant, in one payload.
     *
     * Deliberately NOT getProfileData(): that one is built for a profile
     * screen and costs ~20 queries per user here — User::$appends runs
     * answered_question_ids / questions_complete (each hitting the DB, and
     * the second calls the first again), and its `gallery` key lazy-loads
     * every post the user owns. On a page of 15 applicants that was ~300
     * queries for data nobody reads on this screen.
     *
     * This reads only from relations applications() already eager loaded, so
     * it adds zero queries, and mirrors what the mobile profile screen shows:
     * category, bio, professional details, ratings and the registration Q&A.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function applicantProfile($user)
    {
        $club = $user->relationLoaded('ownedClub') && $user->ownedClub
            ? $user->ownedClub
            : ($user->relationLoaded('club') ? $user->club : null);

        $ratings = $user->relationLoaded('ratingsReceived') ? $user->ratingsReceived : collect();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'image' => $user->profile_photo_path
                ? url('storage/' . $user->profile_photo_path)
                : null,
            'cover_photo' => $user->cover_photo_path
                ? url('storage/' . $user->cover_photo_path)
                : null,
            'bio' => $user->bio,
            'profile_title' => $user->profile_title,
            'birth_date' => $user->birth_date,
            'city' => $user->city,
            'country_id' => $user->country_id !== null ? (int) $user->country_id : null,

            'category' => $user->relationLoaded('category') && $user->category ? [
                'id' => $user->category->id,
                'slug' => $user->category->slug,
                'name' => $user->category->name,
                'name_en' => $user->category->name_en,
                'name_ar' => $user->category->name_ar,
                'display_name_en' => $user->category->display_name_en,
                'display_name_ar' => $user->category->display_name_ar,
            ] : null,

            // The same four fields the mobile profile screen shows under
            // "المعلومات الاحترافية".
            'professional' => [
                'club' => $club ? [
                    'id' => $club->id,
                    'name' => $club->name,
                    'logo' => $club->logo_url,
                ] : null,
                'position' => $user->position,
                'number' => $user->number,
                'nationality' => $user->nationality,
            ],

            'verification_status' => $user->verification_status,
            'is_verified' => $user->verification_status === 'approved',

            // Averaged in PHP from the already-loaded collection - no query.
            'rating_data' => [
                'average_rating' => round((float) $ratings->avg('rating'), 2),
                'total_ratings' => $ratings->count(),
            ],

            // The applicant's answers to their category's registration
            // questions - the richest signal a publisher has about them.
            'questions_data' => $user->relationLoaded('answers')
                ? $user->answers
                    ->filter(fn ($answer) => $answer->question && $answer->is_visible)
                    ->map(function ($answer) {
                        $question = $answer->question->question;

                        return [
                            'question_id' => $answer->question_id,
                            'question' => is_array($question)
                                ? ($question['ar'] ?? $question['en'] ?? '')
                                : (string) $question,
                            'question_en' => is_array($question) ? ($question['en'] ?? null) : (string) $question,
                            'question_ar' => is_array($question) ? ($question['ar'] ?? null) : (string) $question,
                            'answer' => $answer->answer,
                        ];
                    })->values()
                : [],
        ];
    }
}
