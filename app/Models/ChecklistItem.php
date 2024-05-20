<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'checklist_section_id',
        'position',
    ];

    public function checklistSection()
    {
        return $this->belongsTo(ChecklistSection::class);
    }
}
