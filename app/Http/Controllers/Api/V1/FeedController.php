<?php

namespace App\Http\Controllers\Api\V1;

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
     * Get Algorithmic Feed.
     * GET /api/v1/feed
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum');
        $perPage = $request->input('per_page', 10);
        $type = $request->input('type');

        $feed = $this->feedService->getFeed($user, (int) $perPage, $type);

        return response()->json([
            'status' => true,
            'data' => $feed,
            'message' => 'Feed retrieved successfully'
        ]);
    }

    /**
     * Mark post as seen.
     * POST /api/v1/feed/seen
     */
    public function markAsSeen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $postIds = (array) $request->post_ids;

        foreach ($postIds as $postId) {
            $this->feedService->markAsSeen($user, (int) $postId);
        }

        return response()->json([
            'status' => true,
            'message' => 'Posts marked as seen'
        ]);
    }
}
