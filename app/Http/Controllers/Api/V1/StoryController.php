<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;

class StoryController extends Controller
{
    /**
     * Get active stories feed.
     * Returns stories of users the authenticated user follows + own stories.
     * Grouped by user.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Get IDs of users the current user is following
        $followedUserIds = $request->user()->following()->pluck('following_id')->toArray();
        $followedUserIds[] = $userId; // Include self

        $stories = Story::with('user:id,name,email,profile_photo_path')
            ->whereIn('user_id', $followedUserIds)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by user
        $grouped = $stories->groupBy('user_id')->map(function ($userStories) {
            $user = $userStories->first()->user;
            return [
                'user' => $user,
                'stories' => $userStories->map(function ($story) {
                    return [
                        'id' => $story->id,
                        'type' => $story->type,
                        'content' => $story->content,
                        'media_url' => $story->media_url,
                        'expires_at' => $story->expires_at,
                        'created_at' => $story->created_at,
                    ];
                })
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $grouped,
            'message' => 'Stories retrieved successfully'
        ]);
    }

    /**
     * Create a new story.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:text,image,video',
            'content' => 'nullable|string|max:1000',
            'media' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Additional validation based on type
        if ($request->type === 'image' && !$request->hasFile('media')) {
            return response()->json(['status' => false, 'message' => 'Media file is required for image stories.'], 422);
        }
        if ($request->type === 'video' && !$request->hasFile('media')) {
            return response()->json(['status' => false, 'message' => 'Media file is required for video stories.'], 422);
        }
        if ($request->type === 'text' && !$request->filled('content')) {
            return response()->json(['status' => false, 'message' => 'Content is required for text stories.'], 422);
        }

        $mediaPath = null;

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = $file->getClientOriginalExtension();

            if ($request->type === 'image') {
                $validator = Validator::make(['media' => $file], ['media' => 'image|max:10240']); // 10MB
                if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
                
                $mediaPath = $file->store('stories/images', 'public');
            } elseif ($request->type === 'video') {
                $validator = Validator::make(['media' => $file], ['media' => 'mimes:mp4,mov,ogg,qt|max:30720']); // 30MB
                if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
                
                // Store raw video first
                $rawPath = $file->store('stories/videos/raw', 'public');
                
                // Compress video synchronously for stories since they are short and need to be available immediately
                try {
                    $compressedPath = 'stories/videos/' . uniqid() . '.mp4';
                    $singleBitrate = (new X264('aac', 'libx264'))->setKiloBitrate(500)
                        ->setAdditionalParameters(['-preset', 'superfast', '-crf', '28']);
                    
                    FFMpeg::fromDisk('public')
                        ->open($rawPath)
                        ->export()
                        ->toDisk('public')
                        ->inFormat($singleBitrate)
                        ->save($compressedPath);

                    $mediaPath = $compressedPath;
                    Storage::disk('public')->delete($rawPath); // Delete raw
                } catch (\Exception $e) {
                    \Log::error('Story Video Compression Failed: ' . $e->getMessage());
                    // Fallback to raw if compression fails
                    $mediaPath = $rawPath; 
                }
            }
        }

        $story = Story::create([
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'content' => $request->content,
            'media_path' => $mediaPath,
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $story->id,
                'type' => $story->type,
                'content' => $story->content,
                'media_url' => $story->media_url,
                'expires_at' => $story->expires_at,
                'created_at' => $story->created_at,
            ],
            'message' => 'Story created successfully'
        ], 201);
    }

    /**
     * Delete a story early.
     */
    public function destroy(Request $request, $id)
    {
        $story = Story::find($id);

        if (!$story) {
            return response()->json(['status' => false, 'message' => 'Story not found'], 404);
        }

        if ($story->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        $story->delete();

        return response()->json(['status' => true, 'message' => 'Story deleted successfully']);
    }

    /**
     * Mark a story as seen.
     */
    public function markSeen(Request $request, $id)
    {
        $story = Story::find($id);

        if (!$story) {
            return response()->json(['status' => false, 'message' => 'Story not found'], 404);
        }

        StoryView::firstOrCreate([
            'story_id' => $story->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['status' => true, 'message' => 'Story marked as seen']);
    }

    /**
     * Get users who viewed a story.
     */
    public function viewers(Request $request, $id)
    {
        $story = Story::find($id);

        if (!$story) {
            return response()->json(['status' => false, 'message' => 'Story not found'], 404);
        }

        if ($story->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $views = $story->views()->with('user:id,name,email,profile_photo_path')->latest()->get();

        $viewers = $views->map(function ($view) {
            return [
                'user' => $view->user,
                'viewed_at' => $view->created_at,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'views_count' => $viewers->count(),
                'viewers' => $viewers
            ],
            'message' => 'Story viewers retrieved successfully'
        ]);
    }

    /**
     * Update an existing story.
     */
    public function update(Request $request, $id)
    {
        $story = Story::find($id);

        if (!$story) {
            return response()->json(['status' => false, 'message' => 'Story not found'], 404);
        }

        if ($story->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:text,image,video',
            'content' => 'nullable|string|max:1000',
            'media' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $type = $request->input('type', $story->type);

        // Validation based on type
        if ($type === 'image' && !$request->hasFile('media') && !$story->media_path) {
            return response()->json(['status' => false, 'message' => 'Media file is required for image stories.'], 422);
        }
        if ($type === 'video' && !$request->hasFile('media') && !$story->media_path) {
            return response()->json(['status' => false, 'message' => 'Media file is required for video stories.'], 422);
        }
        if ($type === 'text' && !$request->filled('content') && !$request->has('content')) {
            return response()->json(['status' => false, 'message' => 'Content is required for text stories.'], 422);
        }

        $mediaPath = $story->media_path;

        if ($request->hasFile('media')) {
            // Delete old media
            if ($story->media_path) {
                Storage::disk('public')->delete($story->media_path);
            }

            $file = $request->file('media');

            if ($type === 'image') {
                $validator = Validator::make(['media' => $file], ['media' => 'image|max:10240']);
                if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
                
                $mediaPath = $file->store('stories/images', 'public');
            } elseif ($type === 'video') {
                $validator = Validator::make(['media' => $file], ['media' => 'mimes:mp4,mov,ogg,qt|max:30720']);
                if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
                
                $rawPath = $file->store('stories/videos/raw', 'public');
                
                try {
                    $compressedPath = 'stories/videos/' . uniqid() . '.mp4';
                    $singleBitrate = (new X264('aac', 'libx264'))->setKiloBitrate(500)
                        ->setAdditionalParameters(['-preset', 'superfast', '-crf', '28']);
                    
                    FFMpeg::fromDisk('public')
                        ->open($rawPath)
                        ->export()
                        ->toDisk('public')
                        ->inFormat($singleBitrate)
                        ->save($compressedPath);

                    $mediaPath = $compressedPath;
                    Storage::disk('public')->delete($rawPath);
                } catch (\Exception $e) {
                    \Log::error('Story Video Compression Failed: ' . $e->getMessage());
                    $mediaPath = $rawPath; 
                }
            }
        } elseif ($type === 'text' && $story->media_path) {
            // If type changes to text, delete old media
            Storage::disk('public')->delete($story->media_path);
            $mediaPath = null;
        }

        $story->update([
            'type' => $type,
            'content' => $request->has('content') ? $request->content : $story->content,
            'media_path' => $mediaPath,
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $story->id,
                'type' => $story->type,
                'content' => $story->content,
                'media_url' => $story->media_url,
                'expires_at' => $story->expires_at,
                'created_at' => $story->created_at,
            ],
            'message' => 'Story updated successfully'
        ]);
    }
}
