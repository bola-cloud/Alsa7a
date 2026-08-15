<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * List all news (Public).
     */
    public function index(Request $request)
    {
        $query = News::with(['sport', 'media'])
            ->withCount(['likes', 'comments'])
            ->where('is_active', true);

        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        $news = $query->latest()->paginate(10);

        // Check is_liked if user is authenticated
        if ($user = $request->user('sanctum')) {
            $news->through(function ($item) use ($user) {
                $item->is_liked = $item->likes()->where('user_id', $user->id)->exists();
                return $item;
            });
        }

        return response()->json([
            'status' => true,
            'data' => $news,
            'message' => 'News retrieved successfully'
        ]);
    }

    /**
     * Show news details (Public).
     */
    public function show(Request $request, $id)
    {
        // Direct access by id - country filter must not hide it.
        $news = News::directAccess()->with(['sport', 'media', 'comments.user'])
            ->withCount(['likes'])
            ->where('is_active', true)
            ->find($id);

        if (!$news) {
            return response()->json([
                'status' => false,
                'message' => 'News not found'
            ], 404);
        }

        if ($user = $request->user('sanctum')) {
            $news->is_liked = $news->likes()->where('user_id', $user->id)->exists();
        }

        return response()->json([
            'status' => true,
            'data' => $news,
            'message' => 'News details retrieved successfully'
        ]);
    }
}
