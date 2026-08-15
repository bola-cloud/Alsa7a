<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Question;
use App\Models\QuestionAnswer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCountryScope;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasCountryScope;

    /**
     * Users are never filtered by country automatically.
     *
     * The sanctum guard loads the token owner with an Eloquent query, so a
     * global scope here calls the guard from inside the guard (HTTP 500,
     * "Infinite recursion?"), and it would also hide users of other countries
     * from relations such as a post author or a followers list.
     *
     * The country_id column is still filled on creation by HasCountryScope,
     * and the admin panel filters users by country explicitly.
     *
     * @return bool
     */
    protected static function appliesCountryGlobalScope()
    {
        return false;
    }

    /**
     * Scope a query to the country selected in the admin panel.
     *
     * Strict on purpose: unlike API reads (which show NULL/legacy content
     * everywhere for backward compatibility), the admin switcher is meant to
     * isolate one country's users, so picking a country here does not also
     * pull in every user who has no country set.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $countryId  a country id, 'all', or the sentinel 'none'
     *                            for users with no country assigned yet
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCountry($query, $countryId)
    {
        if (! $countryId || $countryId === 'all') {
            return $query;
        }

        if ($countryId === 'none') {
            return $query->whereNull('users.country_id');
        }

        return $query->where('users.country_id', $countryId);
    }

    /**
     * The country the user selected (V2 country filtering, `country_id`).
     *
     * Deliberately NOT named `country()` — the `users` table already has a
     * legacy free-text `country` column, and Eloquent's magic `$user->country`
     * accessor always resolves the raw column over a same-named relation, so
     * a `country()` relation here would silently never be called through
     * property access (only through `$user->country()->first()`).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function selectedCountry()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'password',
        'phone',
        'phone_verified_at',
        'phone_verification_code',
        'category_id',
        // profile / player / provider fields
        'profile_title',
        'bio',
        'rate',
        'rating',
        'city',
        'country',
        'team_id',
        'club_id',
        'position',
        'number',
        'nationality',
        'stats',
        'is_featured',
        'availability',
        'birth_date',
        'cover_photo_path',
        // Verification & Approval
        'is_approved',
        'is_blocked',
        'verification_status',
        'verification_documents',
        'show_services_activity',
        'rejection_reason',
        'onesignal_subscription',
        'parent_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'phone_verification_code',
        'parent_code',
        'verification_documents',
        'profile_photo_path',
        'cover_photo_path',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'provider_id');
    }

    public function serviceRequestsMade()
    {
        return $this->hasMany(ServiceRequest::class, 'requester_id');
    }

    public function serviceRequestsReceived()
    {
        return $this->hasMany(ServiceRequest::class, 'provider_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the club associated with the user.
     * Use 'club_id' for members/players.
     * However, the Club Account Owner is linked via clubs.user_id.
     * We might need a separate relation for "ownedClub".
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function ownedClub()
    {
        return $this->hasOne(Club::class, 'user_id');
    }

    public function clubRequests()
    {
        return $this->hasMany(ClubRequest::class);
    }

    /**
     * Media attached to the user (gallery, certificates, profile media)
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    /**
     * Service reviews written by the user
     */
    public function serviceReviews()
    {
        return $this->hasMany(ServiceReview::class, 'user_id');
    }

    /**
     * Bookings made by the user
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    /**
     * Posts (Gallery)
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Posts (Gallery)
     */
    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function isSubscribed()
    {
        return $this->subscription && $this->subscription->isActive();
    }

    public function ratingsReceived()
    {
        return $this->hasMany(UserRating::class, 'rated_id');
    }

    /**
     * Users satisfying (Followers)
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Users being followed (Following)
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Answers submitted by the user
     */
    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    public function viewedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_views', 'user_id', 'post_id')
            ->withTimestamps();
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'stats' => 'array',
        'availability' => 'array',
        'is_featured' => 'boolean',
        'is_approved' => 'boolean',
        'is_blocked' => 'boolean',
        'is_admin' => 'boolean',
        'show_services_activity' => 'boolean',
        'verification_documents' => 'array',
        'onesignal_subscription' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'cover_photo_url', // Added
        'answered_question_ids',
        'questions_complete',
        'display_name',
        'alsa7a_id',
    ];

    public function getAlsa7aIdAttribute()
    {
        return 100000 + ($this->id * 10);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->ownedClub) {
            return $this->ownedClub->name;
        }
        return $this->name;
    }

    public function getDisplayNameEnAttribute()
    {
        if ($this->ownedClub) {
            return $this->ownedClub->name_en ?: $this->name;
        }
        return $this->name;
    }

    public function getDisplayNameArAttribute()
    {
        if ($this->ownedClub) {
            return $this->ownedClub->name_ar ?: $this->name;
        }
        return $this->name;
    }

    public function getAnsweredQuestionIdsAttribute()
    {
        if (!$this->id || !$this->category_id) {
            return [];
        }

        $questionIds = Question::where('category_id', $this->category_id)->pluck('id')->toArray();
        if (empty($questionIds))
            return [];

        $answered = QuestionAnswer::where('user_id', $this->id)
            ->whereIn('question_id', $questionIds)
            ->pluck('question_id')
            ->unique()
            ->values()
            ->toArray();

        return $answered;
    }

    /**
     * Get the URL to the user's profile photo.
     * Overrides HasProfilePhoto trait.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        $path = $this->profile_photo_path;

        if ($path) {
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }
            return asset('storage/' . $path);
        }

        return $this->defaultProfilePhotoUrl();
    }

    /**
     * Get the URL to the user's cover photo.
     *
     * @return string|null
     */
    public function getCoverPhotoUrlAttribute()
    {
        $path = $this->cover_photo_path;

        if ($path) {
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }
            return asset('storage/' . $path);
        }

        return null;
    }

    public function getQuestionsCompleteAttribute()
    {
        if (!$this->category_id)
            return false;

        $total = Question::where('category_id', $this->category_id)->count();
        if ($total === 0)
            return false;

        $answeredCount = count($this->answered_question_ids ?? []);

        return $answeredCount >= $total;
    }
}
