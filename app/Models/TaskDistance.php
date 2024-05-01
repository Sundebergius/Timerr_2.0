<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDistance extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'distance', 'price_per_km'];

    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }

    public function registrationDistances()
    {
        return $this->hasMany(RegistrationDistance::class, 'task_id');
    }
}
