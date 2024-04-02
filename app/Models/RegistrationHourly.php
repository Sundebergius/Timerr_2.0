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
        'task_id',
        'task_hourly_id',
        'title',
        'seconds_worked',
        'hourly_rate',
        'earnings',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }
}