<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskOther extends Model
{
    use HasFactory;

    protected $table = 'task_other';

    protected $fillable = [
        'title',
        'description',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function task()
    // {
    //     return $this->belongsTo(Task::class);
    // }

    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }
}
