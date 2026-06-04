<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\PostInteractionNotification;

class PostController extends Controller
{
    /**
     * Community Feed (Public/Protected).
     * Lists latest posts.
     */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'comments']) // can limit comments or just load count + latest
            ->withCount(['likes', 'comments'])
            ->where('is_hidden', false)
            ->where('processing_status', 'completed');

        if ($request->has('type') && in_array($request->type, ['image', 'video'])) {
            $query->where('type', $request->type);
        }

        $query->latest();

        $posts = $query->paginate(10);

        // Check statuses
        if ($user = $request->user('sanctum')) {
            $postIds = $posts->getCollection()->pluck('id')->toArray();
            $authorIds = $posts->getCollection()->pluck('user_id')->unique()->toArray();
            
            $likedPostIds = \App\Models\Like::where('user_id', $user->id)
                ->where('likeable_type', Post::class)
                ->whereIn('likeable_id', $postIds)
                ->pluck('likeable_id')->toArray();
                
            $followingIds = $user->following()->whereIn('following_id', $authorIds)->pluck('following_id')->toArray();
            $followerIds = $user->followers()->whereIn('follower_id', $authorIds)->pluck('follower_id')->toArray();

            $posts->getCollection()->each(function ($post) use ($likedPostIds, $followingIds, $followerIds) {
                $post->setAttribute('is_liked', in_array($post->id, $likedPostIds));
                if ($post->user) {
                    $post->user->setAttribute('is_following', in_array($post->user->id, $followingIds));
                    $post->user->setAttribute('is_follower', in_array($post->user->id, $followerIds));
                }
            });
        }

        return response()->json([
            'status' => true,
            'data' => $posts,
            'message' => 'Feed retrieved successfully'
        ]);
    }

    /**
     * List posts by a specific user (Public).
     */
    public function userPosts(Request $request, $id)
    {
        $query = Post::where('user_id', $id)
            ->withCount(['likes', 'comments'])
            ->where('is_hidden', false)
            ->where('processing_status', 'completed');

        if ($request->has('type') && in_array($request->type, ['image', 'video'])) {
            $query->where('type', $request->type);
        }

        $query->latest();

        $posts = $query->paginate(9);

        // Check statuses
        if ($user = $request->user('sanctum')) {
            $postIds = $posts->getCollection()->pluck('id')->toArray();
            $authorIds = [$id]; // Only one author for this API
            
            $likedPostIds = \App\Models\Like::where('user_id', $user->id)
                ->where('likeable_type', Post::class)
                ->whereIn('likeable_id', $postIds)
                ->pluck('likeable_id')->toArray();
                
            $isFollowing = $user->following()->where('following_id', $id)->exists();
            $isFollower = $user->followers()->where('follower_id', $id)->exists();

            $posts->getCollection()->each(function ($post) use ($likedPostIds, $isFollowing, $isFollower) {
                $post->setAttribute('is_liked', in_array($post->id, $likedPostIds));
                if ($post->user) {
                    $post->user->setAttribute('is_following', $isFollowing);
                    $post->user->setAttribute('is_follower', $isFollower);
                }
            });
        }

        return response()->json([
            'status' => true,
            'data' => $posts,
            'message' => 'User posts retrieved successfully'
        ]);
    }

    /**
     * Create a new post (Protected).
     */
    /**
     * Create a new post (Protected).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:10240', // 10MB
            'video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:51200', // 50MB
            'video_thumbnail' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        if (!$request->filled('content') && !$request->hasFile('image') && !$request->hasFile('video')) {
            return response()->json(['status' => false, 'message' => 'Post cannot be empty'], 422);
        }

        $type = 'text';
        $path = null;

        if ($request->hasFile('image')) {
            $type = 'image';
            $path = $request->file('image')->store('posts', 'public');
        } elseif ($request->hasFile('video')) {
            $type = 'video';
            $path = $request->file('video')->store('posts/videos', 'public');

            if ($request->hasFile('video_thumbnail')) {
                $thumbnailPath = 'storage/' . $request->file('video_thumbnail')->store('posts/thumbnails', 'public');
            }
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content', ''),
            'image' => $path, // Used for both image and video path
            'video_thumbnail' => $thumbnailPath ?? null,
            'type' => $type,
            'is_hidden' => false,
            'processing_status' => $type === 'video' ? 'pending' : 'completed',
        ]);

        if ($type === 'video') {
            \App\Jobs\ProcessReelVideo::dispatch($post, 'post');
        }

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => $post->load('user')
        ], 201);
    }

    /**
     * Update a post (Protected).
     */
    public function update(Request $request, $id)
    {
        $post = Post::where('user_id', $request->user()->id)->find($id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found or unauthorized'], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:10240',
            'video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:51200',
            'video_thumbnail' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('image')) {
            // Delete old file
            if ($post->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
            }
            $post->image = $request->file('image')->store('posts', 'public');
            $post->type = 'image';
        } elseif ($request->hasFile('video')) {
            // Delete old file
            if ($post->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
            }
            if ($post->video_thumbnail) {
                $thumbPath = str_replace('storage/', '', $post->video_thumbnail);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($thumbPath);
            }

            $post->image = $request->file('video')->store('posts/videos', 'public');
            $post->type = 'video';

            if ($request->hasFile('video_thumbnail')) {
                $post->video_thumbnail = 'storage/' . $request->file('video_thumbnail')->store('posts/thumbnails', 'public');
            }
        }

        if ($request->has('content')) {
            $post->content = $request->input('content', '');
        }

        // If content is empty and no file exists (and user didn't upload new one), validation logic might be needed
        // but let's assume if they update content to empty, they must have a file, or if they delete file...
        // For now preventing completely empty post
        if (empty($post->content) && empty($post->image)) {
            return response()->json(['status' => false, 'message' => 'Post cannot be empty'], 422);
        }

        $post->save();

        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully',
            'data' => $post->load('user')
        ]);
    }

    /**
     * Show post details (Public).
     */
    public function show(Request $request, $id)
    {
        $post = Post::with(['user', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->where('is_hidden', false)
            ->where('processing_status', 'completed')
            ->find($id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        if ($user = $request->user('sanctum')) {
            $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
            if ($post->user) {
                $post->user->is_following = $user->following()->where('following_id', $post->user_id)->exists();
                $post->user->is_follower = $user->followers()->where('follower_id', $post->user_id)->exists();
            }
        }

        return response()->json([
            'status' => true,
            'data' => $post
        ]);
    }

    /**
     * Delete Post (Protected).
     */
    public function destroy(Request $request, $id)
    {
        $post = Post::where('user_id', $request->user()->id)->find($id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found or unauthorized'], 404);
        }

        $post->delete();

        return response()->json(['status' => true, 'message' => 'Post deleted successfully']);
    }

    /**
     * Toggle Like (Protected).
     */
    public function like(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post)
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);

        $user = $request->user();
        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $status = 'unliked';
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            $status = 'liked';

            // Notify Post Owner
            try {
                if ($post->user_id !== $user->id) {
                    $displayName = $user->display_name;
                    $displayNameEn = $user->display_name_en;
                    $displayNameAr = $user->display_name_ar;

                    $post->user->notify(new PostInteractionNotification($post, [
                        'title' => 'New Like',
                        'body' => "{$displayName} liked your post.",
                        'interaction_type' => 'like',
                        'push_title' => ['en' => 'New Like', 'ar' => 'إعجاب جديد'],
                        'push_body' => ['en' => "{$displayNameEn} liked your post.", 'ar' => "قام {$displayNameAr} بالإعجاب بمنشورك."]
                    ]));
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => [
                'status' => $status,
                'likes_count' => $post->likes()->count()
            ]
        ]);
    }

    /**
     * Get Users who liked the post (Paginated).
     */
    public function likes(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post)
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);

        $likes = $post->likes()
            ->with('user:id,name,email,profile_photo_path,profile_title') // Eager load user
            ->paginate(15);

        // Transform to return user objects directly if preferred, or keep as like objects with user relation
        // Let's return the likers directly to match followers/following structure if possible,
        // but since we are paginating on 'likes' table, we will return like objects containing users.
        // Frontend can map it.

        return response()->json([
            'status' => true,
            'data' => $likes,
            'message' => 'Post likes retrieved successfully'
        ]);
    }

    /**
     * Add Comment (Protected).
     */
    public function comment(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post)
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body
        ]);

        // Notify Post Owner
        try {
            $user = $request->user();
            if ($post->user_id !== $user->id) {
                $displayName = $user->display_name;
                $displayNameEn = $user->display_name_en;
                $displayNameAr = $user->display_name_ar;

                $post->user->notify(new PostInteractionNotification($post, [
                    'title' => 'New Comment',
                    'body' => "{$displayName} commented on your post.",
                    'interaction_type' => 'comment',
                    'push_title' => ['en' => 'New Comment', 'ar' => 'تعليق جديد'],
                    'push_body' => ['en' => "{$displayNameEn} commented on your post.", 'ar' => "قام {$displayNameAr} بالتعليق على منشورك."]
                ]));
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return response()->json([
            'status' => true,
            'message' => 'Comment added',
            'data' => $comment->load('user')
        ], 201);
    }

    /**
     * Get Reels Feed (Only videos, completed processing).
     */
    public function reels(Request $request)
    {
        $query = Post::with(['user', 'comments'])
            ->withCount(['likes', 'comments'])
            ->where('is_hidden', false)
            ->where('type', 'video')
            ->where('processing_status', 'completed')
            ->latest();

        $posts = $query->paginate(10);

        if ($user = $request->user('sanctum')) {
            $postIds = $posts->getCollection()->pluck('id')->toArray();
            $authorIds = $posts->getCollection()->pluck('user_id')->unique()->toArray();
            
            $likedPostIds = \App\Models\Like::where('user_id', $user->id)
                ->where('likeable_type', Post::class)
                ->whereIn('likeable_id', $postIds)
                ->pluck('likeable_id')->toArray();
                
            $followingIds = $user->following()->whereIn('following_id', $authorIds)->pluck('following_id')->toArray();
            $followerIds = $user->followers()->whereIn('follower_id', $authorIds)->pluck('follower_id')->toArray();

            $posts->getCollection()->each(function ($post) use ($likedPostIds, $followingIds, $followerIds) {
                $post->setAttribute('is_liked', in_array($post->id, $likedPostIds));
                if ($post->user) {
                    $post->user->setAttribute('is_following', in_array($post->user->id, $followingIds));
                    $post->user->setAttribute('is_follower', in_array($post->user->id, $followerIds));
                }
            });
        }

        return response()->json([
            'status' => true,
            'data' => $posts,
            'message' => 'Reels retrieved successfully'
        ]);
    }

    /**
     * Increment view count for a Reel / Post.
     */
    public function incrementView($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        $post->increment('views_count');

        return response()->json([
            'status' => true,
            'message' => 'View incremented successfully',
            'views_count' => $post->views_count
        ]);
    }
}
