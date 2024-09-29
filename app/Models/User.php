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
use Laravel\Cashier\Billable;
use App\Services\PlanService;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'user_id');
    }

    public function currentTeam()
    {
        // Assuming you have a 'current_team_id' column in your users table
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all teams the user belongs to.
     */
    // public function teams()
    // {
    //     return $this->hasMany(Team::class);
    // }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user'); // Assuming you have a pivot table named 'team_user'
    }

    // Add this method to your User model
    public function subscriptionPlan(): string
    {
        // Inject PlanService
        $planService = app(PlanService::class);

        // Get the user's active subscription
        $subscription = $this->subscription('default'); // 'default' is the subscription name in Cashier

        // If the user doesn't have a subscription, default to 'free'
        if (!$subscription || !$subscription->active()) {
            return 'free';
        }

        // Get the Stripe price ID from the subscription
        $stripePriceId = $subscription->stripe_price;

        // Use PlanService to get the plan name by price ID
        return $planService->getPlanNameByPriceId($stripePriceId);
    }

    public function subscriptionItems()
    {
        return $this->hasMany(\Laravel\Cashier\SubscriptionItem::class);
    }

    // public function subscriptions()
    // {
    //     // A user has many subscriptions
    //     return $this->hasMany(Subscription::class);
    // }

    // Check if the user is subscribed to a specific plan
    public function isSubscribedTo($planName)
    {
        return $this->subscribedToPlan($planName);
    }

    // Check if the user has any active subscription
    public function hasActiveSubscription()
    {
        return $this->subscribed();
    }
}
