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
     * Create a new post (Protected).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:10240', // 10MB
            'video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        if (!$request->filled('content') && !$request->hasFile('image') && !$request->hasFile('video')) {
            return response()->json(['status' => false, 'message' => 'Post cannot be empty'], 422);
        }

        $postData = [
            'user_id' => $request->user()->id,
            'content' => $request->input('content', ''),
            'is_hidden' => false,
        ];

        if ($request->hasFile('image')) {
            $postData['image'] = $request->file('image')->store('posts', 'public');
        } elseif ($request->hasFile('video')) {
            $postData['image'] = $request->file('video')->store('posts/videos', 'public');
        }

        $post = Post::create($postData);

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => $post->load('user')
        ], 201);
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
