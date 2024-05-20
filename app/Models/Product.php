<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'description',
        'image',
        'price',
        'quantityInStock',
        'quantitySold',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_product')
                    ->withPivot('total_sold');
    }
}
