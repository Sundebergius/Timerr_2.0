<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'task_type',
        'taskable_id',
        'taskable_type',
        'client_id',
    ];

    const TYPE_PROJECT_BASED = 1;
    const TYPE_HOURLY = 2;
    const TYPE_PRODUCT = 3;
    const TYPE_DISTANCE = 4;
    const TYPE_OTHER = 5;

    public static function boot()
    {
        parent::boot();

        static::deleting(function($task) {
            switch ($task->task_type) {
                case self::TYPE_PROJECT_BASED:
                case self::TYPE_HOURLY:
                case self::TYPE_PRODUCT:
                case self::TYPE_DISTANCE:
                case self::TYPE_OTHER:
                    $task->taskable->deleteWithRegistrations();
                    break;
            }
        });
    }

    public function taskable()
    {
        return $this->morphTo();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function taskProject()
    {
        return $this->hasOne(TaskProject::class);
    }
    
    public function taskHourly()
    {
        return $this->hasOne(TaskHourly::class);
    }

    public function taskDistance()
    {
        return $this->hasOne(TaskDistance::class);
    }

    public function taskProduct()
    {
        return $this->hasMany(TaskProduct::class);
    }

    public function taskOther()
    {
        return $this->hasOne(TaskOther::class);
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class);
    }

    public function checklistSections()
    {
        return $this->hasMany(ChecklistSection::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'task_product')
                    ->withPivot('total_sold');
    }
}
