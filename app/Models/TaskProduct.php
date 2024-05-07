<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'title',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // public function task()
    // {
    //     return $this->belongsTo(Task::class);
    // }

    public function task()
    {
        return $this->morphOne(Task::class, 'taskable');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
