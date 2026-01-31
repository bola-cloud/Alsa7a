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
use App\Models\Project;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

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
        'verification_status',
        'verification_documents',
        'rejection_reason',
        'onesignal_subscription',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'profile_photo_path',
        'cover_photo_path', // Added to hidden
    ];

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
        'is_admin' => 'boolean',
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
    ];

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
