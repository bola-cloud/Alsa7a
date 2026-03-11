<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostView;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\FormatsProfileData;

class FeedService
{
    use FormatsProfileData;

    /**
     * Get Algorithmic Feed for a User.
     * Logic:
     * 1. Unseen posts from Followed users (Latest)
     * 2. Unseen posts from non-followed users (Suggestions)
     * 3. Fallback to seen posts if all content is exhausted.
     */
    public function getFeed(?User $user = null, int $perPage = 10)
    {
        // Base unseen query for both authenticated and guests
        $baseUnseen = Post::with(['user.category', 'user.club', 'user.ownedClub', 'comments'])
            ->withCount(['likes', 'comments'])
            ->where('is_hidden', false)
            ->latest();

        if ($user) {
            $viewedPostIds = $user->viewedPosts()->pluck('post_id')->toArray();
            $followingIds = $user->following()->pluck('following_id')->toArray();
            $baseUnseenUser = (clone $baseUnseen)->whereNotIn('id', $viewedPostIds);

            // 1. Get Unseen posts from Followed users
            $followedUnseen = (clone $baseUnseenUser)->whereIn('user_id', $followingIds);

            // 2. Get Unseen posts from non-followed (Suggestions)
            $suggestedUnseen = (clone $baseUnseenUser)->whereNotIn('user_id', array_merge($followingIds, [$user->id]));

            // Combine using Union or separate queries depending on count
            $results = $followedUnseen->get()->merge($suggestedUnseen->get());

            // 3. Fallback: If results are few, add seen posts back with priority
            if ($results->count() < $perPage) {
                // Seen posts from followed users (Latest)
                $seenFollowed = Post::with(['user.category', 'user.club', 'user.ownedClub', 'comments'])
                    ->withCount(['likes', 'comments'])
                    ->whereIn('user_id', $followingIds)
                    ->whereIn('id', $viewedPostIds)
                    ->where('is_hidden', false)
                    ->latest()
                    ->limit($perPage)
                    ->get();

                // Seen posts from others (Suggestions)
                $seenSuggested = Post::with(['user.category', 'user.club', 'user.ownedClub', 'comments'])
                    ->withCount(['likes', 'comments'])
                    ->whereNotIn('user_id', array_merge($followingIds, [$user->id]))
                    ->whereIn('id', $viewedPostIds)
                    ->where('is_hidden', false)
                    ->latest()
                    ->limit($perPage)
                    ->get();

                $results = $results->merge($seenFollowed)->merge($seenSuggested)->unique('id');
            }

            // Manual Pagination for memory collections
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $results->slice(($currentPage - 1) * $perPage, $perPage)->values()->all();

            $paginated = new LengthAwarePaginator(
                $currentItems,
                $results->count(),
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            // Guest Feed Logic: Native pagination on latest posts (order of posting)
            $paginated = $baseUnseen->paginate($perPage);
        }

        // Unique users processing to avoid redundant work and errors
        $usersToProcess = collect();
        foreach ($paginated as $post) {
            if ($post->user) $usersToProcess->put($post->user->id, $post->user);
        }

        $usersToProcess->each(function ($userObj) use ($user) {
            if (is_object($userObj)) {
                // Ensure legacy fields match requested format
                $userObj->image = $userObj->profile_photo_url;
                if ($userObj->profile_photo_path) {
                    $url = url('storage/' . $userObj->profile_photo_path);
                    $userObj->image = $url;
                    $userObj->profile_photo_url = $url;
                }

                // Apply trait data
                $profileData = $this->getProfileData($userObj, false, $user);
                foreach ($profileData as $key => $value) {
                    if (!is_array($userObj->{$key})) {
                        $userObj->{$key} = $value;
                    }
                }
            }
        });

        // Add is_liked status
        $paginated->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked = $user ? $post->likes()->where('user_id', $user->id)->exists() : false;
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
