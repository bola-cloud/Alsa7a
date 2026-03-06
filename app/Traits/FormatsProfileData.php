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
                'id' => $user->category->id,
                'name' => $user->category->name,
                'is_service_provider' => $user->category->is_service_provider,
                'requires_verification' => (bool) $user->category->requires_verification,
                'verification_requirements_en' => $user->category->verification_requirements_en,
                'verification_requirements_ar' => $user->category->verification_requirements_ar,
                'verification_fields' => $user->category->verification_fields,
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
                    'user_id' => $user->club->user_id,
                ] : null,
                'team_id' => $user->team_id,
                'position' => $user->position,
                'number' => $user->number,
                'nationality' => $user->nationality,
                'stats' => $user->stats,
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
            'is_club_account' => $user->is_club_account ?? ($user->club ? ($user->club->user_id === $user->id) : false),
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

        // Specialized Club Account View
        if ($data['is_club_account']) {
            $club = $user->club ?? $user->ownedClub;
            if ($club) {
                $data['club_details'] = [
                    'id' => $club->id,
                    'name' => $club->name,
                    'logo' => $club->logo_url,
                    'banner' => $club->banner_url,
                ];

                if ($user->relationLoaded('club.teams')) {
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
        }

        return $data;
    }
}
