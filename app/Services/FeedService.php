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
    public function getFeed(?User $user = null, int $perPage = 10, ?string $type = null, ?int $countryId = null)
    {
        $baseQuery = Post::with([
                'user.category', 'user.club', 'user.ownedClub', 'comments',
                'images',
                'mentions:id,name,profile_photo_path', // Only load safe fields for mentions
            ])
            ->withCount(['likes', 'comments'])
            ->where('is_hidden', false)
            ->where('processing_status', 'completed');

        if ($type && in_array($type, ['image', 'video'])) {
            $baseQuery->where('type', $type);
        }

        if ($countryId) {
            $baseQuery->where('country_id', $countryId);
        }

        if ($user) {
            $followingIds = $user->following()->pluck('following_id')->toArray();
            $followingIds[] = $user->id; // Include self
            $followingIdsStr = implode(',', $followingIds);
            $userId = $user->id;

            $baseQuery->addSelect('posts.*')
                ->selectRaw("EXISTS(SELECT 1 FROM post_views WHERE post_views.post_id = posts.id AND post_views.user_id = ?) as is_seen", [$userId])
                ->selectRaw("IF(posts.user_id IN ($followingIdsStr), 1, 0) as is_following_or_self")
                ->orderBy('is_seen', 'asc') // Unseen first (0), then seen (1)
                ->orderBy('is_following_or_self', 'desc') // Followed first (1), then suggestions (0)
                ->latest('posts.created_at');

            $paginated = $baseQuery->paginate($perPage);
        } else {
            // Guest Feed Logic: Native pagination on latest posts (order of posting)
            $paginated = $baseQuery->latest()->paginate($perPage);
        }

        // Unique users processing to avoid redundant work and errors
        $usersToProcess = collect();
        foreach ($paginated as $post) {
            if ($post->user) $usersToProcess->put($post->user->id, $post->user);
        }

        // Pre-fetch following/follower data for the current page users
        $followingIds = $user ? $user->following()->whereIn('following_id', $usersToProcess->keys())->pluck('following_id')->toArray() : [];
        $followerIds = $user ? $user->followers()->whereIn('follower_id', $usersToProcess->keys())->pluck('follower_id')->toArray() : [];
        
        $usersToProcess->each(function ($userObj) use ($user, $followingIds, $followerIds) {
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

                // Add follow status
                $userObj->setAttribute('is_following', in_array($userObj->id, $followingIds));
                $userObj->setAttribute('is_follower', in_array($userObj->id, $followerIds));
            }
        });

        // Pre-fetch liked posts IDs for this collection to avoid N+1 and potential false negatives
        $postIdsInPage = $paginated->getCollection()->pluck('id')->toArray();
        $likedPostIds = $user ? \App\Models\Like::where('user_id', $user->id)
            ->where('likeable_type', Post::class)
            ->whereIn('likeable_id', $postIdsInPage)
            ->pluck('likeable_id')
            ->toArray() : [];

        // Add is_liked status
        $paginated->getCollection()->transform(function ($post) use ($user, $likedPostIds) {
            $post->setAttribute('is_liked', in_array($post->id, $likedPostIds));
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
