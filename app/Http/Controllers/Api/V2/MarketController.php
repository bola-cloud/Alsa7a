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
     * Get Marketplace requests filtered by Country
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = MarketRequest::with(['club', 'category'])
            ->where('status', 'active');
            
        // Filter by user's country if set
        if ($user->country_id) {
            $query->where('country_id', $user->country_id);
        }

        $requests = $query->latest()->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Market requests retrieved successfully.'
        ]);
    }

    /**
     * Create a new Market Request (Job Post)
     * Assuming the authenticated user is the club owner.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user owns a club
        if (!$user->ownedClub) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have a club to create a request for.'
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
            'club_id' => $user->ownedClub->id,
            'category_id' => $request->category_id,
            'country_id' => $user->country_id, // Inherit country from club owner
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => true,
            'data' => $marketRequest,
            'message' => 'Market request created successfully.'
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
            'message' => 'Application submitted successfully. The club owner will contact you via chat.'
        ]);
    }

    /**
     * Get Market Requests created by my Club
     */
    public function myRequests(Request $request)
    {
        $user = $request->user();

        if (!$user->ownedClub) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have a club.'
            ], 403);
        }

        $requests = MarketRequest::with(['category', 'applications.user'])
            ->where('club_id', $user->ownedClub->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Your market requests retrieved successfully.'
        ]);
    }
}
