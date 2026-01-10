<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * List Comments for a specific Post.
     * GET /posts/{id}/comments
     */
    public function index($postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        $comments = $post->comments()
            ->with(['user:id,name,profile_photo_path'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $comments,
            'message' => 'Comments retrieved successfully'
        ]);
    }

    /**
     * Store a new Comment.
     * POST /posts/{id}/comments
     */
    public function store(Request $request, $postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

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
            'message' => 'Comment created successfully',
            'data' => $comment->load('user:id,name,profile_photo_path')
        ], 201);
    }

    /**
     * Update an existing Comment.
     * PUT/POST /comments/{id}
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['status' => false, 'message' => 'Comment not found'], 404);
        }

        // Authorization: Only owner can update
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $comment->update(['body' => $request->body]);

        return response()->json([
            'status' => true,
            'message' => 'Comment updated successfully',
            'data' => $comment->load('user:id,name,profile_photo_path')
        ]);
    }

    /**
     * Delete a Comment.
     * DELETE /comments/{id}
     */
    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['status' => false, 'message' => 'Comment not found'], 404);
        }

        // Authorization: Owner or Post Owner (Optional logic, sticking to comment owner for now)
        // If we want post owner to delete comments too:
        // $post = $comment->post;
        // if ($comment->user_id !== $request->user()->id && $post->user_id !== $request->user()->id) ...

        if ($comment->user_id !== $request->user()->id) {
            // Let's check if the user owns the post the comment is on (moderation)
            $post = $comment->post;
            if ($post && $post->user_id === $request->user()->id) {
                // Allowed (Post owner deleting a comment on their post)
            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        $comment->delete();

        return response()->json([
            'status' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
