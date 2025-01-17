<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;
use App\Models\User;

class RegistrationProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_project_id',
        'title',
        'type',
        'description',
        'date',
        'amount',
        'currency',
        'location',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taskProject()
    {
        return $this->belongsTo(TaskProject::class, 'task_project_id');
    }
}
