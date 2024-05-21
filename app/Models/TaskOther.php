<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomField;
use App\Models\ChecklistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskOther extends Model
{
    public $timestamps = false;
    
    use HasFactory;

    protected $table = 'task_other';

    protected $fillable = [
        'description',
    ];
    
    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class, 'task_id');
    }

    public function checklistSections()
    {
        return $this->hasMany(ChecklistSection::class, 'task_id');
    }

    public function deleteWithRegistrations()
    {
        Log::info('deleteWithRegistrations called');

        try {
            DB::transaction(function () {
                $this->customFields()->delete();
                $this->checklistSections()->each(function ($section) {
                    $section->checklistItems()->delete();
                    $section->delete();
                });
                $this->delete();
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete task, custom fields, and checklist items: ' . $e->getMessage());
        }
    }
}
