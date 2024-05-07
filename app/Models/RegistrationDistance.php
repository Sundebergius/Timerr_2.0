<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationDistance extends Model
{
    use HasFactory;

    protected $table = 'registration_distance';

    protected $fillable = [
        'user_id',
        'task_id',
        'title',
        'distance',
    ];

    public function taskDistance()
    {
        return $this->belongsTo(TaskDistance::class, 'task_id');
    }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
