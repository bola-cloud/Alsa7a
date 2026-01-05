<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    /**
     * List Community Categories (Public).
     */
    public function getCategories()
    {
        $categories = CommunityCategory::all();
        // Since we use Translatable trait, 'name' attribute should be automatically handled if accessed,
        // but for API we might want to append it or just let the trait magic work accessing $cat->name

        $categories->transform(function ($cat) {
            $cat->name = $cat->name; // Trigger accessor
            return $cat;
        });

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }

    /**
     * List Community Posts (Public).
     * Filters: category_id
     */
    public function index(Request $request)
    {
        $query = CommunityPost::with(['user', 'category'])
            ->where('is_hidden', false)
            ->latest();

        if ($request->has('category_id')) {
            $query->where('community_category_id', $request->category_id);
        }

        $posts = $query->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $posts,
            'message' => 'Community posts retrieved successfully'
        ]);
    }

    /**
     * Create Community Post (Protected).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'community_category_id' => 'required|exists:community_categories,id',
            'content' => 'required|string',
            'image' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('community', 'public');
        }

        $post = CommunityPost::create([
            'user_id' => $request->user()->id,
            'community_category_id' => $request->input('community_category_id'),
            'content' => $request->input('content'),
            'image' => $path,
            'is_hidden' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => $post->load('user', 'category')
        ], 201);
    }

    /**
     * Get Single Post
     */
    public function show($id)
    {
        $post = CommunityPost::with(['user', 'category'])->where('is_hidden', false)->find($id);
        if (!$post)
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);

        return response()->json(['status' => true, 'data' => $post]);
    }

    /**
     * Delete Post
     */
    public function destroy(Request $request, $id)
    {
        $post = CommunityPost::where('user_id', $request->user()->id)->find($id);
        if (!$post)
            return response()->json(['status' => false, 'message' => 'Not found or unauthorized'], 404);

        if ($post->image)
            Storage::disk('public')->delete($post->image);
        $post->delete();

        return response()->json(['status' => true, 'message' => 'Deleted successfully']);
    }

    /**
     * Update Post
     */
    public function update(Request $request, $id)
    {
        $post = CommunityPost::where('user_id', $request->user()->id)->find($id);
        if (!$post)
            return response()->json(['status' => false, 'message' => 'Not found or unauthorized'], 404);

        $validator = Validator::make($request->all(), [
            'community_category_id' => 'nullable|exists:community_categories,id',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails())
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        if ($request->has('community_category_id'))
            $post->community_category_id = $request->input('community_category_id');
        if ($request->has('content'))
            $post->content = $request->input('content');

        if ($request->hasFile('image')) {
            if ($post->image)
                Storage::disk('public')->delete($post->image);
            $post->image = $request->file('image')->store('community', 'public');
        }

        $post->save();

        return response()->json(['status' => true, 'message' => 'Updated successfully', 'data' => $post->load('category')]);
    }
}
