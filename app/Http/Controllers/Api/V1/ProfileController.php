<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRating;
use App\Models\QuestionAnswer;
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
        $currentUser = $request->user('sanctum');
        $isFollowing = false;
        if ($currentUser) {
            $isFollowing = $currentUser->following()->where('following_id', $user->id)->exists();
        }

        return $this->formatProfileResponse($user, $isFollowing, $currentUser);
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
     * Internal helper to get raw profile data array.
     */
    protected function getProfileData($user, $isFollowing, $currentUser = null)
    {
        // Check if viewing own profile
        $isMe = $currentUser && $currentUser->id === $user->id;

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->email,
            'email' => $user->email, // Added explicit email usually good
            'phone' => $user->phone, // Added phone number
            'birth_date' => $user->birth_date, // Added birth_date
            'profile_title' => $user->profile_title,
            'bio' => $user->bio,
            'image' => $user->profile_photo_path ? url('storage/' . $user->profile_photo_path) : $user->profile_photo_url,
            'cover_photo' => $user->cover_photo_path ? url('storage/' . $user->cover_photo_path) : null,
            'category' => $user->category ? [
                'id' => $user->category->id,
                'name' => $user->category->name,
                'is_service_provider' => $user->category->is_service_provider,
                'parent_category_id' => $user->category->parent_category_id,
                'parent_category' => $user->category->parentCategory ? [
                    'id' => $user->category->parentCategory->id,
                    'name' => $user->category->parentCategory->name,
                    'image' => $user->category->parentCategory->image ? url('storage/' . $user->category->parentCategory->image) : null,
                ] : null,
            ] : null,

            // Professional Details
            'professional' => [
                'club' => $user->club ? [
                    'id' => $user->club->id,
                    'name' => $user->club->name,
                    'logo' => $user->club->logo_url,
                    'user_id' => $user->club->user_id, // Club Owner User ID
                ] : null,
                'team_id' => $user->team_id,
                'position' => $user->position,
                'number' => $user->number,
                'nationality' => $user->nationality,
                'stats' => $user->stats,
            ],

            // Questions & Answers (Detailed List)
            'questions_data' => $user->answers->map(function ($answer) {
                $q = $answer->question;

                // Logic to extract en/ar question text
                $qRaw = $q->getAttributes()['question'] ?? '';
                $qData = $q->question;

                $questionEn = null;
                $questionAr = null;
                $mainQuestion = '';

                if (is_array($qData)) {
                    $questionEn = $qData['en'] ?? null;
                    $questionAr = $qData['ar'] ?? null;
                    $mainQuestion = !empty($questionEn) ? $questionEn : ($questionAr ?? '');
                } else {
                    $mainQuestion = (string) $qRaw;
                    $questionEn = $mainQuestion;
                    $questionAr = $mainQuestion;
                }

                // Logic to extract choices
                $choicesData = $q->choices;
                $choices = [];
                $choicesEn = [];
                $choicesAr = [];

                if (is_array($choicesData) && !empty($choicesData)) {
                    $choices = array_values($choicesData);
                    $choicesEn = array_keys($choicesData);
                    $choicesAr = array_values($choicesData);
                }

                return [
                    'question_id' => $answer->question_id,
                    'question' => $mainQuestion,
                    'question_en' => $questionEn,
                    'question_ar' => $questionAr,
                    'type' => $q->type ?? null,
                    'choices' => $choices,
                    'choices_en' => $choicesEn,
                    'choices_ar' => $choicesAr,
                    'answer' => $answer->answer,
                    'is_visible' => (bool) $answer->is_visible,
                ];
            })->filter(function ($item) use ($isMe) {
                // If it's my profile, I see everything.
                // Otherwise, only show if the individual answer is visible.
                return $isMe || $item['is_visible'];
            })->values(),

            'rating_data' => [
                'average_rating' => (float) $user->ratingsReceived()->avg('rating'),
                'total_ratings' => $user->ratingsReceived()->count(),
            ],

            'stats' => [ // Social Stats
                'posts' => $user->posts_count,
                'followers' => $user->followers_count,
                'following' => $user->following_count,
            ],
            'is_following' => $isFollowing,
            'is_club_account' => $user->club ? ($user->club->user_id === $user->id) : false,
            'role_in_club' => (function () use ($user) {
                if (!$user->club_id && !$user->ownedClub)
                    return null;
                if ($user->ownedClub || ($user->club && $user->club->user_id === $user->id))
                    return 'admin';
                return 'member';
            })(),
            'club_relationship' => (function () use ($user, $currentUser) {
                // If viewer is not logged in or doesn't own a club, return null
                if (!$currentUser || !$currentUser->ownedClub) {
                    return null;
                }

                $myClubId = $currentUser->ownedClub->id;

                // 1. Check if user is already a member
                if ($user->club_id == $myClubId) {
                    return 'member';
                }

                // 2. Check for pending requests
                $pendingRequest = \App\Models\ClubRequest::where('club_id', $myClubId)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->first();

                if ($pendingRequest) {
                    return $pendingRequest->type === 'invite' ? 'invite_pending' : 'join_pending';
                }

                return 'none';
            })(),
        ];

        // Specialized Club Account View
        if ($data['is_club_account']) {
            $club = $user->club;
            $data['club_details'] = [
                'id' => $club->id,
                'name' => $club->name,
                'logo' => $club->logo_url,
                'banner' => $club->banner_url,
                'teams' => $club->teams()->with('members:id,name,email,profile_photo_path,position,number,team_id')->get()->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'age_group' => $team->age_group,
                        'image' => $team->image ? url('storage/' . $team->image) : null,
                        'members' => $team->members->map(function ($member) {
                            return [
                                'id' => $member->id,
                                'name' => $member->name,
                                'image' => $member->profile_photo_url,
                                'position' => $member->position,
                                'number' => $member->number,
                            ];
                        })
                    ];
                }),
            ];
        }

        // Add private/progress info if it's my profile
        if ($isMe) {
            $data['answered_question_ids'] = $user->answered_question_ids;
            $data['questions_complete'] = (bool) $user->questions_complete;

            // Verification Status
            $data['verification_status'] = $user->verification_status; // 'pending', 'approved', 'rejected', null
            $data['is_verified'] = ($user->verification_status === 'approved');

            // Club Requests (Pending)
            // If Club Admin: Show people wanting to join
            if ($data['is_club_account']) {
                $data['pending_join_requests'] = \App\Models\ClubRequest::where('club_id', $user->club->id)
                    ->where('type', 'join')
                    ->where('status', 'pending')
                    ->with('user:id,name,email,profile_photo_path') // minimal user info
                    ->get()
                    ->transform(function ($req) {
                        $req->user->image = $req->user->profile_photo_url;
                        return $req;
                    });
            } else {
                // If Regular User: Show clubs inviting me
                $data['pending_club_invites'] = \App\Models\ClubRequest::where('user_id', $user->id)
                    ->where('type', 'invite')
                    ->where('status', 'pending')
                    ->with('club:id,name,logo_url')
                    ->get();
            }
        }

        return $data;
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
            'category_id' => 'nullable|exists:categories,id', // Added
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
        if ($request->has('bio'))
            $user->bio = $request->bio;
        if ($request->has('profile_title'))
            $user->profile_title = $request->profile_title;
        if ($request->has('category_id'))
            $user->category_id = $request->category_id; // Added

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

        return $this->formatProfileResponse($user, true, $user);
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

        UserRating::updateOrCreate(
            [
                'reviewer_id' => $currentUser->id,
                'rated_id' => $targetUser->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

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
            ->with(['ownedClub', 'club', 'category.parentCategory', 'answers.question'])
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
            ->with(['ownedClub', 'club', 'category.parentCategory', 'answers.question'])
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
