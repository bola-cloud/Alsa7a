<?php

namespace App\Traits;

use App\Models\User;
use App\Models\ClubRequest;

trait FormatsProfileData
{
    /**
     * Internal helper to get raw profile data array.
     * Matches logic from ProfileController.
     */
    protected function getProfileData(User $user, $isFollowing = false, $currentUser = null)
    {
        // Check if viewing own profile
        $isMe = $currentUser && $currentUser->id === $user->id;

        $associatedClub = $user->ownedClub ?: $user->club;

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->email,
            'email' => $user->email,
            'phone' => $user->phone,
            'birth_date' => $user->birth_date,
            'profile_title' => $user->profile_title,
            'bio' => $user->bio,
            'image' => $user->profile_photo_path ? url('storage/' . $user->profile_photo_path) : $user->profile_photo_url,
            'cover_photo' => $user->cover_photo_path ? url('storage/' . $user->cover_photo_path) : null,
            'category' => $user->category ? [
                'id' => data_get($user->category, 'id'),
                'name' => data_get($user->category, 'name'),
                'name_en' => data_get($user->category, 'name_en'), // Restored
                'name_ar' => data_get($user->category, 'name_ar'), // Restored
                'is_service_provider' => data_get($user->category, 'is_service_provider'),
                'requires_verification' => (bool) data_get($user->category, 'requires_verification'),
                'verification_requirements_en' => data_get($user->category, 'verification_requirements_en'),
                'verification_requirements_ar' => data_get($user->category, 'verification_requirements_ar'),
                'verification_fields' => data_get($user->category, 'verification_fields'),
                'parent_category_id' => data_get($user->category, 'parent_category_id'),
                'parent_category' => data_get($user->category, 'parentCategory') ? [
                    'id' => data_get($user->category, 'parentCategory.id'),
                    'name' => data_get($user->category, 'parentCategory.name'),
                    'image' => data_get($user->category, 'parentCategory.image') ? url('storage/' . data_get($user->category, 'parentCategory.image')) : null,
                ] : null,
            ] : null,
 
            // Professional Details
            'professional' => [
                'club' => $associatedClub ? [
                    'id' => $associatedClub->id,
                    'name' => $associatedClub->name,
                    'logo' => $associatedClub->logo_url,
                    'user_id' => $associatedClub->user_id,
                ] : null,
                'team_id' => $user->team_id,
                'position' => $user->position,
                'number' => $user->number,
                'nationality' => $user->nationality,
                'stats' => $user->stats,
                'show_answers' => true, // Restored (Default to true for backward compatibility)
            ],

            // Questions & Answers
            'questions_data' => $user->relationLoaded('answers') ? $user->answers->map(function ($answer) {
                $q = $answer->question;
                if (!$q)
                    return null;

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
                if (!$item)
                    return false;
                return $isMe || $item['is_visible'];
            })->values() : [],

            'answered_question_ids' => $user->answered_question_ids ?? [], // Restored
            'questions_complete' => (bool)($user->questions_complete ?? false), // Restored
            
            'gallery' => $user->posts ? $user->posts->map(function ($post) { // Restored
                return [
                    'id' => $post->id,
                    'image' => (strpos($post->image, 'http') === 0) ? $post->image : url('storage/' . $post->image),
                    'video_thumbnail' => $post->video_thumbnail ? url('storage/' . $post->video_thumbnail) : null, // Added
                    'content' => $post->content,
                ];
            }) : [],

            'rating_data' => [
                'average_rating' => (float) ($user->relationLoaded('ratingsReceived') ? $user->ratingsReceived()->avg('rating') : 0),
                'total_ratings' => $user->relationLoaded('ratingsReceived') ? $user->ratingsReceived()->count() : 0,
            ],

            'stats' => [
                'posts' => $user->posts_count ?? 0,
                'followers' => $user->followers_count ?? 0,
                'following' => $user->following_count ?? 0,
            ],
            'is_following' => (bool) $isFollowing,
            'is_club_account' => $user->is_club_account ?? ($associatedClub ? ($associatedClub->user_id === $user->id) : false),
            'verification_status' => $user->verification_status, // Restored
            'is_verified' => $user->verification_status === 'approved', // Restored
            'is_blocked' => (bool) $user->is_blocked, // Added
            'show_services_activity' => (bool) $user->show_services_activity, // Added
            
            'address' => $user->address, // Added
            'city' => $user->city, // Added
            'country' => $user->country, // Added
            'currency' => $user->currency, // Added (if exists on user)

            'role_in_club' => (function () use ($user) {
                if (!$user->club_id && !$user->ownedClub)
                    return null;
                if ($user->ownedClub || ($user->club && $user->club->user_id === $user->id))
                    return 'admin';
                return 'member';
            })(),
            'club_relationship' => (function () use ($user, $currentUser) {
                if (!$currentUser || !$currentUser->ownedClub) {
                    return null;
                }
                $myClubId = $currentUser->ownedClub->id;
                if ($user->club_id == $myClubId) {
                    return 'member';
                }
                $pendingRequest = ClubRequest::where('club_id', $myClubId)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->first();
                if ($pendingRequest) {
                    return $pendingRequest->type === 'invite' ? 'invite_pending' : 'join_pending';
                }
                return 'none';
            })(),
            'subscription' => [
                'is_subscribed' => $user->isSubscribed(),
                'type' => $user->subscription ? $user->subscription->type : null,
                'end_date' => $user->subscription ? $user->subscription->end_date : null,
                'status' => $user->subscription ? $user->subscription->status : null,
            ],
        ];

        // Club Details View (Always include if associated with a club, either as owner or member)
        $club = $user->ownedClub ?: $user->club;
        if ($club) {
            $data['club_details'] = [
                'id' => data_get($club, 'id'),
                'name' => data_get($club, 'name'),
                'logo' => data_get($club, 'logo_url') ?: data_get($club, 'logo'), // Handle both model and array
                'banner' => data_get($club, 'banner_url') ?: data_get($club, 'banner'),
                'user_id' => data_get($club, 'user_id'),
            ];

            if (is_object($club) && ($user->relationLoaded('club.teams') || $user->relationLoaded('ownedClub.teams'))) {
                $data['club_details']['teams'] = $club->teams()->with('members:id,name,email,profile_photo_path,position,number,team_id')->get()->map(function ($team) {
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
                });
            }
        }

        return $data;
    }
}
