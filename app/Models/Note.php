<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'content'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
