<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;
use App\Models\User;

class RegistrationHourly extends Model
{
    use HasFactory;

    protected $table = 'registration_hourly';

    protected $fillable = [
        'user_id',
        'task_hourly_id',
        'minutes_worked',
        'earnings',
        'comment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taskHourly()
    {
        return $this->belongsTo(TaskHourly::class, 'task_hourly_id');
    }
}