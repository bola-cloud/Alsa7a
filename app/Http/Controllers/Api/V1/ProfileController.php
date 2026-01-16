<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Get Current Authenticated User Profile (Protected).
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Eager load relationships
        $user->loadCount(['followers', 'following', 'posts']);
        $user->load([
            'club',
            'category',
            'answers.question' // Load answers with their questions
        ]);

        return $this->formatProfileResponse($user, true);
    }

    /**
     * Get User Profile (Public).
     */
    public function show(Request $request, $id)
    {
        $user = User::withCount(['followers', 'following', 'posts'])
            ->with([
                'club',
                'category',
                'answers.question' // Added answers
            ])
            ->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if authenticated user is following this profile
        $isFollowing = false;
        if ($request->user('sanctum')) {
            $isFollowing = $request->user('sanctum')->following()->where('following_id', $user->id)->exists();
        }

        return $this->formatProfileResponse($user, $isFollowing);
    }

    /**
     * Helper to format profile response.
     */
    protected function formatProfileResponse($user, $isFollowing)
    {
        // Check if viewing own profile
        $isMe = request()->user() && request()->user()->id === $user->id;

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->email,
            'email' => $user->email, // Added explicit email usually good
            'phone' => $user->phone, // Added phone number
            'profile_title' => $user->profile_title,
            'bio' => $user->bio,
            'image' => $user->profile_photo_path ? url('storage/' . $user->profile_photo_path) : $user->profile_photo_url,
            'cover_photo' => $user->cover_photo_path ? url('storage/' . $user->cover_photo_path) : null,
            'category' => $user->category ? [
                'id' => $user->category->id,
                'name' => $user->category->name,
                'is_service_provider' => $user->category->is_service_provider
            ] : null,

            // Professional Details
            'professional' => [
                'club' => $user->club ? [
                    'id' => $user->club->id,
                    'name' => $user->club->name,
                    'logo' => $user->club->logo_url
                ] : null,
                'team_id' => $user->team_id,
                'position' => $user->position,
                'number' => $user->number,
                'nationality' => $user->nationality,
                'stats' => $user->stats,
            ],

            // Questions & Answers (Detailed List)
            'questions_data' => $user->answers->map(function ($answer) {
                return [
                    'question_id' => $answer->question_id,
                    'question' => $answer->question->question ?? null,
                    'type' => $answer->question->type ?? null,
                    'answer' => $answer->answer,
                ];
            }),

            'stats' => [ // Social Stats
                'posts' => $user->posts_count,
                'followers' => $user->followers_count,
                'following' => $user->following_count,
            ],
            'is_following' => $isFollowing,
        ];

        // Add private/progress info if it's my profile
        if ($isMe) {
            $data['answered_question_ids'] = $user->answered_question_ids;
            $data['questions_complete'] = (bool) $user->questions_complete;
        }

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Profile retrieved successfully'
        ]);
    }

    /**
     * Update Profile (Protected).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'profile_title' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:4096', // Profile Photo
            'cover_photo' => 'nullable|image|max:4096', // Cover Photo
            'gallery_images.*' => 'nullable|image|max:4096', // For adding to gallery
            'gallery_videos.*' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:20480', // Gallery videos
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Basic Info
        if ($request->has('name'))
            $user->name = $request->name;
        if ($request->has('email'))
            $user->email = $request->email;
        if ($request->has('phone'))
            $user->phone = $request->phone;
        if ($request->filled('password'))
            $user->password = bcrypt($request->password);
        if ($request->has('birth_date'))
            $user->birth_date = $request->birth_date;
        if ($request->has('country'))
            $user->country = $request->country;
        if ($request->has('bio'))
            $user->bio = $request->bio;
        if ($request->has('profile_title'))
            $user->profile_title = $request->profile_title;

        // Profile Photo
        if ($request->hasFile('image')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('image')->store('profile-photos', 'public');
        }

        // Cover Photo
        if ($request->hasFile('cover_photo')) {
            if ($user->cover_photo_path) {
                Storage::disk('public')->delete($user->cover_photo_path);
            }
            $user->cover_photo_path = $request->file('cover_photo')->store('cover-photos', 'public');
        }

        // Handle Gallery Uploads (Create Posts)
        // Treating "Add Photo"/"Add Video" as creating a new Post

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('posts', 'public');
                $user->posts()->create([
                    'image' => $path,
                    'content' => '', // Empty content for direct gallery upload
                    'is_hidden' => false,
                ]);
            }
        }

        if ($request->hasFile('gallery_videos')) {
            foreach ($request->file('gallery_videos') as $file) {
                $path = $file->store('posts/videos', 'public');
                $user->posts()->create([
                    'image' => $path, // Using image column for media path
                    'content' => 'video', // Marking as video
                    'is_hidden' => false,
                ]);
            }
        }

        $user->save();

        return $this->formatProfileResponse($user, true);
    }

    /**
     * Toggle Follow User (Protected).
     */
    public function follow(Request $request, $id)
    {
        $targetUser = User::find($id);
        $currentUser = $request->user();

        if (!$targetUser) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        if ($targetUser->id === $currentUser->id) {
            return response()->json(['status' => false, 'message' => 'Cannot follow yourself'], 400);
        }

        // Toggle logic
        $isFollowing = $currentUser->following()->where('following_id', $targetUser->id)->exists();

        if ($isFollowing) {
            $currentUser->following()->detach($targetUser->id);
            $message = 'Unfollowed successfully';
            $status = 'unfollowed';
        } else {
            $currentUser->following()->attach($targetUser->id);
            $message = 'Followed successfully';
            $status = 'followed';
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => ['status' => $status]
        ]);
    }

    /**
     * Get User Followers (Paginated).
     */
    public function followers(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $followers = $user->followers()
            ->select('users.id', 'users.name', 'users.email', 'users.profile_photo_path', 'users.profile_title')
            ->paginate(15);

        // Check if I am following them (for the button state)
        if ($currentUser = $request->user('sanctum')) {
            // We need to check if *I* follow *them* (the people in the list)
            // This can be N+1 if not careful.
            // For simplicity, let's just return the list first.
            // Optimally:
            $myFollowingIds = $currentUser->following()->pluck('users.id')->toArray();
        }

        return response()->json([
            'status' => true,
            'data' => $followers,
            'my_following_ids' => $myFollowingIds ?? [], // Send IDs I follow to frontend to check "is_following" status for each
            'message' => 'Followers retrieved successfully'
        ]);
    }

    /**
     * Get User Following (Paginated).
     */
    public function following(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $following = $user->following()
            ->select('users.id', 'users.name', 'users.email', 'users.profile_photo_path', 'users.profile_title')
            ->paginate(15);

        if ($currentUser = $request->user('sanctum')) {
            $myFollowingIds = $currentUser->following()->pluck('users.id')->toArray();
        }

        return response()->json([
            'status' => true,
            'data' => $following,
            'my_following_ids' => $myFollowingIds ?? [],
            'message' => 'Following retrieved successfully'
        ]);
    }
}
