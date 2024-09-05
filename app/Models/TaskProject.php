<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskProject extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'price',
        'currency',
        'project_location',
    ];

    // Specify the attributes that should be cast to Carbon instances
    // protected $dates = [
    //     'start_date',
    //     'end_date',
    //];

    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }

    public function registrationProjects()
    {
        return $this->hasMany(RegistrationProject::class, 'task_project_id');
    }

    public function deleteWithRegistrations()
    {
        Log::info('deleteWithRegistrations called');

        try {
            DB::transaction(function () {
                $this->registrationProjects()->delete();
                $this->delete();
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete task and registrations: ' . $e->getMessage());
        }
    }
}
