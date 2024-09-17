<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskProduct extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $fillable = [
        'task_id',
        'product_id',
        'type',  // Add this
        'quantity',  // Add this
        'attributes',  // Add this if you plan to mass-assign JSON data
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function registrationProducts()
    {
        return $this->hasMany(RegistrationProduct::class, 'task_product_id');
    }

    public function deleteWithRegistrations()
    {
        Log::info('deleteWithRegistrations called');

        try {
            DB::transaction(function () {
                $this->registrationProducts()->delete();
                $this->delete();
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete task and registrations: ' . $e->getMessage());
        }
    }
}
