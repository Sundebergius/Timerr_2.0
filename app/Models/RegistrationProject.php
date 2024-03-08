<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'price',
        'project_location',
    ];
}
