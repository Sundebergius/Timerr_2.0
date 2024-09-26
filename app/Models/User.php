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

    // public function subscription($name = 'default')
    // {
    //     return $this->hasOne(Subscription::class)->where('name', $name);
    // }

    public function isSubscribedTo($planName)
    {
        return $this->subscribed('default') && $this->subscription('default')->name === $planName;
    }
}
