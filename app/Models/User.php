<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    // Implement the required methods for JWTSubject

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $guarded = [];

    // /**
    //  * The attributes that are mass assignable.
    //  *
    //  * @var array<int, string>
    //  */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    // /**
    //  * The attributes that should be hidden for serialization.
    //  *
    //  * @var array<int, string>
    //  */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    //     'two_factor_recovery_codes',
    //     'two_factor_secret',
    // ];

    // /**
    //  * The accessors to append to the model's array form.
    //  *
    //  * @var array<int, string>
    //  */
    // protected $appends = [
    //     'profile_photo_url',
    // ];

    // /**
    //  * Get the attributes that should be cast.
    //  *
    //  * @return array<string, string>
    //  */
    // protected function casts(): array
    // {
    //     return [
    //         'email_verified_at' => 'datetime',
    //         'password' => 'hashed',
    //     ];
    // }

    public function scopeWithRatingStats($query)
    {
        return $query->withAvg(['assets as assets_rating_avg' => fn($q) => $q->where('asset_status_id', 1)->where('visibility', true)], 'rating')
            ->withAvg(['templates as templates_rating_avg' => fn($q) => $q->where('template_status_id', 1)], 'rating')
            ->withSum(['assets as assets_reviews_sum' => fn($q) => $q->where('asset_status_id', 1)->where('visibility', true)], 'review_count')
            ->withSum(['templates as templates_reviews_sum' => fn($q) => $q->where('template_status_id', 1)], 'review_count');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notification_settings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function user_type()
    {
        return $this->belongsTo(UserType::class);
    }

    public function account_type()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function offer_type()
    {
        return $this->belongsTo(OfferType::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function bank_accounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function user_skills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function user_portfolios()
    {
        return $this->hasMany(UserPortfolio::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function group_subscriptions()
    {
        return $this->hasMany(GroupSubscription::class);
    }

    public function site_jobs()
    {
        return $this->hasMany(SiteJob::class);
    }

    public function site_job_applications()
    {
        return $this->hasMany(SiteJobApplication::class);
    }

    public function user_subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function escrows()
    {
        return $this->hasMany(Escrow::class);
    }

    public function customization_chats_as_client()
    {
        return $this->hasMany(CustomizationChat::class, 'client_user_id');
    }

    public function customization_chats_as_owner()
    {
        return $this->hasMany(CustomizationChat::class, 'owner_user_id');
    }

    public function customization_chat_messages()
    {
        return $this->hasMany(CustomizationChatMessage::class, 'sender_user_id');
    }

    public function customization_revisions_requested()
    {
        return $this->hasMany(CustomizationRevision::class, 'requested_by_user_id');
    }

    public function customization_revisions_responded()
    {
        return $this->hasMany(CustomizationRevision::class, 'responded_by_user_id');
    }

    public function referred_by()
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }

    public function affiliate_commissions_earned()
    {
        return $this->hasMany(AffiliateCommission::class, 'referrer_user_id');
    }

    public function affiliate_commissions_generated()
    {
        return $this->hasMany(AffiliateCommission::class, 'referred_user_id');
    }
}
