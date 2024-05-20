<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDistance extends Model
{
    public $timestamps = false;
    
    use HasFactory;

    protected $table = 'task_distance';

    protected $fillable = ['distance', 'price_per_km'];

    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }

    public function registrationDistances()
    {
        return $this->hasMany(RegistrationDistance::class, 'task_distance_id');
    }

    public function deleteWithRegistrations()
    {
        Log::info('deleteWithRegistrations called');

        try {
            DB::transaction(function () {
                $this->registrationDistances()->delete();
                $this->delete();
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete task and registrations: ' . $e->getMessage());
        }
    }
}
