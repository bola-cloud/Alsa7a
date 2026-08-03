<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedController extends Controller
{
    protected $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    /**
     * Get Algorithmic Feed Filtered by User's Country
     * GET /api/v2/feed
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum');
        $perPage = $request->input('per_page', 10);
        $type = $request->input('type'); // null = all types (image, video, text)
        
        // Use user's country_id for filtering
        $countryId = $user ? $user->country_id : null;

        $feed = $this->feedService->getFeed($user, (int) $perPage, $type, $countryId);

        return response()->json([
            'status' => true,
            'data' => $feed,
            'message' => 'Feed retrieved successfully (Filtered by Country)'
        ]);
    }
}
