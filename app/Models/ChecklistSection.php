<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'task_id',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class, 'checklist_section_id');
    }
}
