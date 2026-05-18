<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Notifications\PostInteractionNotification;
use App\Traits\FormatsProfileData;

class CommunityController extends Controller
{
    use FormatsProfileData;
    /**
     * List Community Categories (Public).
     */
    public function getCategories()
    {
        $categories = CommunityCategory::all();
        // Since we use Translatable trait, 'name' attribute should be automatically handled if accessed,
        // but for API we might want to append it or just let the trait magic work accessing $cat->name

        $categories->transform(function ($cat) {
            $cat->name = $cat->name_en; // Trigger accessor or use name_en
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
        $user = $request->user('sanctum');

        $query = CommunityPost::with(['user.category', 'user.club', 'user.ownedClub', 'category'])
            ->withCount(['comments', 'likes'])
            ->where('is_hidden', false)
            ->where('processing_status', 'completed')
            ->latest();

        if ($request->has('category_id')) {
            $query->where('community_category_id', $request->category_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $posts = $query->paginate(10);

        // Unique users processing to avoid redundant work and errors
        $usersToProcess = collect();
        foreach ($posts as $post) {
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

        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked = $user ? $post->likes()->where('user_id', $user->id)->exists() : false;
            return $post;
        });

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
            'video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:51200',
            'video_thumbnail' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $path = null;
        $thumbnailPath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('community', 'public');
        } elseif ($request->hasFile('video')) {
            $path = $request->file('video')->store('community/videos', 'public');

            if ($request->hasFile('video_thumbnail')) {
                $thumbnailPath = 'storage/' . $request->file('video_thumbnail')->store('community/thumbnails', 'public');
            }
        }

        $post = CommunityPost::create([
            'user_id' => $request->user()->id,
            'community_category_id' => $request->input('community_category_id'),
            'content' => $request->input('content'),
            'image' => $path,
            'video_thumbnail' => $thumbnailPath,
            'is_hidden' => false,
            'processing_status' => $request->hasFile('video') ? 'pending' : 'completed',
        ]);

        if ($request->hasFile('video')) {
            \App\Jobs\ProcessReelVideo::dispatch($post, 'community');
        }

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => $post->load('user', 'category')
        ], 201);
    }

    /**
     * Get Single Post
     */
    public function show(Request $request, $id)
    {
        $user = $request->user('sanctum');

        $post = CommunityPost::with(['user', 'category'])
            ->withCount(['comments', 'likes'])
            ->where('is_hidden', false)
            ->where('processing_status', 'completed')
            ->find($id);

        if (!$post)
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);

        $post->is_liked = $user ? $post->likes()->where('user_id', $user->id)->exists() : false;

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
            'video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:51200',
            'video_thumbnail' => 'nullable|image|max:5120',
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

            // Delete old thumbnail if switching from video to image
            if ($post->video_thumbnail) {
                $thumbPath = str_replace('storage/', '', $post->video_thumbnail);
                Storage::disk('public')->delete($thumbPath);
                $post->video_thumbnail = null;
            }

            $post->image = $request->file('image')->store('community', 'public');
        } elseif ($request->hasFile('video')) {
            if ($post->image)
                Storage::disk('public')->delete($post->image);

            if ($post->video_thumbnail) {
                $thumbPath = str_replace('storage/', '', $post->video_thumbnail);
                Storage::disk('public')->delete($thumbPath);
            }

            $post->image = $request->file('video')->store('community/videos', 'public');

            if ($request->hasFile('video_thumbnail')) {
                $post->video_thumbnail = 'storage/' . $request->file('video_thumbnail')->store('community/thumbnails', 'public');
            }
        }

        $post->save();

        return response()->json(['status' => true, 'message' => 'Updated successfully', 'data' => $post->load('category')]);
    }

    /**
     * Toggle Like on Community Post
     */
    public function like(Request $request, $id)
    {
        $post = CommunityPost::find($id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        $user = $request->user();
        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            return response()->json(['status' => true, 'message' => 'Unliked', 'is_liked' => false]);
        } else {
            $post->likes()->create(['user_id' => $user->id]);

            // Notify Post Owner
            try {
                if ($post->user_id !== $user->id) {
                    $post->user->notify(new PostInteractionNotification($post, [
                        'title' => 'New Like',
                        'body' => "{$user->name} liked your community post.",
                        'interaction_type' => 'like',
                        'push_title' => ['en' => 'New Like', 'ar' => 'إعجاب جديد'],
                        'push_body' => ['en' => "{$user->name} liked your community post.", 'ar' => "قام {$user->name} بالإعجاب بمنشورك في المجتمع."]
                    ]));
                }
            } catch (\Exception $e) {
                // Ignore
            }

            return response()->json(['status' => true, 'message' => 'Liked', 'is_liked' => true]);
        }
    }

    /**
     * Get Comments for Community Post
     */
    public function getComments(Request $request, $id)
    {
        $post = CommunityPost::find($id);

        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Post not found'], 404);
        }

        $comments = $post->comments()
            ->with(['user.category', 'user.club', 'user.ownedClub'])
            ->latest()
            ->paginate(20);

        // Unique users processing
        $usersToProcess = collect();
        foreach ($comments as $comment) {
            if ($comment->user) $usersToProcess->put($comment->user->id, $comment->user);
        }

        $currentUser = auth()->user();
        $usersToProcess->each(function ($userObj) use ($currentUser) {
            if (is_object($userObj)) {
                $profileData = $this->getProfileData($userObj, false, $currentUser);
                foreach ($profileData as $key => $value) {
                    if (!is_array($userObj->{$key})) {
                        $userObj->{$key} = $value;
                    }
                }
            }
        });

        return response()->json([
            'status' => true,
            'data' => $comments,
            'message' => 'Comments retrieved successfully'
        ]);
    }

    /**
     * Add Comment to Community Post
     */
    public function comment(Request $request, $id)
    {
        $post = CommunityPost::find($id);

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

        // Notify Post Owner
        try {
            $user = $request->user();
            if ($post->user_id !== $user->id) {
                $post->user->notify(new PostInteractionNotification($post, [
                    'title' => 'New Comment',
                    'body' => "{$user->name} commented on your community post.",
                    'interaction_type' => 'comment',
                    'push_title' => ['en' => 'New Comment', 'ar' => 'تعليق جديد'],
                    'push_body' => ['en' => "{$user->name} commented on your community post.", 'ar' => "قام {$user->name} بالتعليق على منشورك في المجتمع."]
                ]));
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return response()->json([
            'status' => true,
            'message' => 'Comment created successfully',
            'data' => $comment->load('user:id,name,profile_photo_path')
        ], 201);
    }
}
