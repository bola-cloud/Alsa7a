<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\MarketRequest;
use App\Models\MarketApplication;
use Illuminate\Http\Request;
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
        $query = MarketRequest::with(['user', 'club', 'category'])
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
        $marketRequest = MarketRequest::directAccess()->with(['user', 'club', 'category'])
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

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $marketRequest = MarketRequest::create([
            'user_id' => $user->id,
            'club_id' => optional($user->ownedClub)->id,
            'category_id' => $request->category_id,
            'country_id' => $user->country_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => true,
            'data' => $marketRequest->load(['user', 'club', 'category']),
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cvPath = null;
        if ($request->hasFile('cv_file')) {
            $cvPath = $request->file('cv_file')->store('cvs', 'public');
        }

        $application = MarketApplication::create([
            'market_request_id' => $marketRequest->id,
            'user_id' => $user->id,
            'notes' => $request->notes,
            'cv_path' => $cvPath,
        ]);

        return response()->json([
            'status' => true,
            'data' => $application,
            'message' => 'Application submitted successfully. The job poster will contact you via chat.'
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
            ->with('user')
            ->latest()
            ->paginate(15);

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

        $requests = MarketRequest::with(['category', 'club'])
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
}
