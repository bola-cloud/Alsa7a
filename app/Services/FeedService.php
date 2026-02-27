<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostView;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class FeedService
{
    /**
     * Get Algorithmic Feed for a User.
     * Logic:
     * 1. Unseen posts from Followed users (Latest)
     * 2. Unseen posts from non-followed users (Suggestions)
     * 3. Fallback to seen posts if all content is exhausted.
     */
    public function getFeed(User $user, int $perPage = 10)
    {
        $viewedPostIds = $user->viewedPosts()->pluck('post_id')->toArray();
        $followingIds = $user->following()->pluck('following_id')->toArray();

        // 1. Get Unseen posts from Followed users
        $followedUnseen = Post::with(['user', 'comments'])
            ->withCount(['likes', 'comments'])
            ->whereIn('user_id', $followingIds)
            ->whereNotIn('id', $viewedPostIds)
            ->where('is_hidden', false)
            ->latest();

        // 2. Get Unseen posts from non-followed (Suggestions)
        $suggestedUnseen = Post::with(['user', 'comments'])
            ->withCount(['likes', 'comments'])
            ->whereNotIn('user_id', array_merge($followingIds, [$user->id]))
            ->whereNotIn('id', $viewedPostIds)
            ->where('is_hidden', false)
            ->latest();

        // Combine using Union or separate queries depending on count
        // For simple algorithm, we merge them but keep order
        $results = $followedUnseen->get()->merge($suggestedUnseen->get());

        // 3. Fallback: If results are few, add some seen posts back (shuffle for freshness)
        if ($results->count() < $perPage) {
            $seenPosts = Post::with(['user', 'comments'])
                ->withCount(['likes', 'comments'])
                ->whereIn('id', $viewedPostIds)
                ->where('is_hidden', false)
                ->inRandomOrder()
                ->limit($perPage)
                ->get();

            $results = $results->merge($seenPosts)->unique('id');
        }

        // Manual Pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $results->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $paginated = new LengthAwarePaginator(
            $currentItems,
            $results->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // Add is_liked status
        $paginated->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
            return $post;
        });

        return $paginated;
    }

    /**
     * Mark a post as seen by the user.
     */
    public function markAsSeen(User $user, int $postId)
    {
        return PostView::updateOrCreate([
            'user_id' => $user->id,
            'post_id' => $postId
        ]);
    }
}
