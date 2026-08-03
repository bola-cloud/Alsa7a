<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRating;
use App\Models\QuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use App\Traits\FormatsProfileData;
use App\Notifications\FollowNotification;
use App\Notifications\RatingNotification;

class ProfileController extends Controller
{
    use FormatsProfileData;
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
            'ownedClub',
            'category',
            'answers.question', // Load answers with their questions
            'subscription',
            'posts' => function ($query) {
                $query->where('is_hidden', false)
                      ->where('processing_status', 'completed')
                      ->with(['images', 'mentions:id,name,profile_photo_path', 'user', 'likes' => function ($q) {
                          $q->where('user_id', auth()->id());
                      }])
                      ->withCount(['likes', 'comments'])
                      ->latest()
                      ->take(10);
            }
        ]);

        return $this->formatProfileResponse($user, true, $user);
    }

    /**
     * Get User Profile (Public).
     */
    public function show(Request $request, $id)
    {
        $user = User::withCount(['followers', 'following', 'posts'])
            ->with([
                'club',
                'ownedClub',
                'category',
                'answers.question', // Added answers
                'subscription',
                'posts' => function ($query) {
                    $query->where('is_hidden', false)
                          ->where('processing_status', 'completed')
                          ->with(['images', 'mentions:id,name,profile_photo_path', 'user', 'likes' => function ($q) {
                              $q->where('user_id', auth()->id());
                          }])
                          ->withCount(['likes', 'comments'])
                          ->latest()
                          ->take(10);
                }
            ])
            ->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if authenticated user is following this profile
        $currentUser = $request->user('sanctum');
        $isFollowing = false;
        if ($currentUser) {
            $isFollowing = $currentUser->following()->where('following_id', $user->id)->exists();
            
            // Track Profile Visit
            if ($currentUser->id !== $user->id) {
                $recentVisit = \App\Models\ProfileVisit::where('visitor_id', $currentUser->id)
                    ->where('visited_id', $user->id)
                    ->where('updated_at', '>=', now()->subHours(24))
                    ->first();

                if ($recentVisit) {
                    $recentVisit->touch();
                } else {
                    \App\Models\ProfileVisit::create([
                        'visitor_id' => $currentUser->id,
                        'visited_id' => $user->id,
                    ]);
                }
            }
        }

        return $this->formatProfileResponse($user, $isFollowing, $currentUser);
    }

    /**
     * Get list of users who visited my profile.
     */
    public function visitors(Request $request)
    {
        $user = $request->user();

        $visits = \App\Models\ProfileVisit::with('visitor:id,name,email,profile_photo_path,phone')
            ->where('visited_id', $user->id)
            ->latest('updated_at')
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $visits,
            'message' => 'Profile visitors retrieved successfully'
        ]);
    }

    /**
     * Helper to format profile response.
     */
    protected function formatProfileResponse($user, $isFollowing, $currentUser = null)
    {
        return response()->json([
            'status' => true,
            'data' => $this->getProfileData($user, $isFollowing, $currentUser),
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
            'country_id' => 'nullable|exists:countries,id',
            'bio' => 'nullable|string|max:1000',
            'profile_title' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'show_services_activity' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096', // Profile Photo
            'cover_photo' => 'nullable|image|max:4096', // Cover Photo
            'gallery_images.*' => 'nullable|image|max:4096', // For adding to gallery
            'gallery_videos.*' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:20480', // Gallery videos
            'answers' => 'nullable|array', // Added
            'answers.*.question_id' => 'required_with:answers|exists:questions,id',
            'answers.*.answer' => 'required_with:answers',
            'answers.*.is_visible' => 'nullable|boolean',
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
        if ($request->has('country_id'))
            $user->country_id = $request->country_id;
        if ($request->has('bio'))
            $user->bio = $request->bio;
        if ($request->has('profile_title'))
            $user->profile_title = $request->profile_title;
        if ($request->has('category_id'))
            $user->category_id = $request->category_id;
        if ($request->has('show_services_activity'))
            $user->show_services_activity = $request->show_services_activity;

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

        // Handle Registration Answers Update
        if ($request->has('answers')) {
            foreach ($request->answers as $answerData) {
                $dataToUpdate = [
                    'answer' => $answerData['answer']
                ];
                if (isset($answerData['is_visible'])) {
                    $dataToUpdate['is_visible'] = $answerData['is_visible'];
                }
                QuestionAnswer::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'question_id' => $answerData['question_id']
                    ],
                    $dataToUpdate
                );
            }
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $this->getProfileData($user, false, $user)
        ]);
    }

    /**
     * Delete User Account (Protected).
     * Required for Apple App Store compliance.
     */
    public function destroyAccount(Request $request)
    {
        $user = $request->user();

        // Optional: Delete profile/cover photos from storage
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
        if ($user->cover_photo_path) {
            Storage::disk('public')->delete($user->cover_photo_path);
        }

        // Revoke all tokens to log the user out everywhere
        $user->tokens()->delete();

        // Delete the user record
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Rate a user profile.
     */
    public function rate(Request $request, $id)
    {
        $targetUser = User::find($id);
        $currentUser = $request->user();

        if (!$targetUser) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        if ($targetUser->id === $currentUser->id) {
            return response()->json(['status' => false, 'message' => 'Cannot rate yourself'], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $rating = UserRating::updateOrCreate(
            [
                'reviewer_id' => $currentUser->id,
                'rated_id' => $targetUser->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        // Notify Target User
        try {
            $targetUser->notify(new RatingNotification($rating->load('reviewer')));
        } catch (\Exception $e) {
            // Ignore
        }

        return response()->json([
            'status' => true,
            'message' => 'Rating submitted successfully'
        ]);
    }

    /**
     * Get user profile ratings.
     */
    public function ratings(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $ratings = UserRating::where('rated_id', $user->id)
            ->with('reviewer:id,name,profile_photo_path')
            ->latest()
            ->paginate(15);

        $ratings->getCollection()->transform(function ($rating) {
            return [
                'id' => $rating->id,
                'reviewer_id' => $rating->reviewer_id,
                'reviewer_name' => $rating->reviewer->name,
                'reviewer_image' => $rating->reviewer->profile_photo_url,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'created_at' => $rating->created_at,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $ratings,
            'message' => 'Ratings retrieved successfully'
        ]);
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

            // Notify Target User
            try {
                $targetUser->notify(new FollowNotification($currentUser));
            } catch (\Exception $e) {
                // Ignore
            }
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
            ->with(['ownedClub', 'club', 'category.parentCategory', 'answers.question', 'subscription'])
            ->paginate(15);

        // Transform collection using the standard profile formatting (ARRAY ONLY to avoid nesting)
        $followers->getCollection()->transform(function ($follower) use ($request) {
            return $this->getProfileData($follower, false, $request->user('sanctum'));
        });

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
            ->with(['ownedClub', 'club', 'category.parentCategory', 'answers.question', 'subscription'])
            ->paginate(15);

        // Transform collection using the standard profile formatting (ARRAY ONLY to avoid nesting)
        $following->getCollection()->transform(function ($follow) use ($request) {
            return $this->getProfileData($follow, false, $request->user('sanctum'));
        });

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
