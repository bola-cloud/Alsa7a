<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            ->latest();

        $posts = $query->paginate(10);

        // Check is_liked status
        if ($user = $request->user('sanctum')) {
            $updatedItems = $posts->getCollection()->map(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                return $post;
            });
            $posts->setCollection($updatedItems);
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
            ->latest();

        $posts = $query->paginate(9); // Pagination as requested

        // Check is_liked status
        if ($user = $request->user('sanctum')) {
            $updatedItems = $posts->getCollection()->map(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                return $post;
            });
            $posts->setCollection($updatedItems);
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
        ]);

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
            ->find($id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        if ($user = $request->user('sanctum')) {
            $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
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

        return response()->json([
            'status' => true,
            'message' => 'Comment added',
            'data' => $comment->load('user')
        ], 201);
    }
}
