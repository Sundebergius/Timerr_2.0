<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'title', 'task_type', 'user_id', 'taskable_id', 'taskable_type'];

    const TYPE_PROJECT_BASED = 1;
    const TYPE_HOURLY = 2;
    const TYPE_PRODUCT = 3;
    const TYPE_DISTANCE = 4;
    const TYPE_OTHER = 5;

    public function taskable()
    {
        return $this->morphTo();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function taskProject()
    {
        return $this->hasOne(TaskProject::class);
    }
    
    public function taskHourly()
    {
        return $this->hasOne(TaskHourly::class);
    }
}
