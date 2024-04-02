<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskHourly extends Model
{
    use HasFactory;
    protected $table = 'task_hourly';
    protected $fillable = [
        'title',
        'rate_per_hour',
        'rate_per_minute',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function registrationHourly()
    {
        return $this->hasMany(RegistrationHourly::class);
    }
}