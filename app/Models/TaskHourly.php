<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskHourly extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'task_hourly';

    protected $fillable = [
        'rate_per_hour',
        'rate_per_minute',
    ];

    public function deleteWithRegistrations()
    {
        Log::info('deleteWithRegistrations called');

        try {
            DB::transaction(function () {
                $this->registrationHourly()->delete();
                $this->delete();
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete task and registrations: ' . $e->getMessage());
        }
    }
    
    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }

    public function registrationHourly()
    {
        return $this->hasMany(RegistrationHourly::class, 'task_hourly_id');
    }
}