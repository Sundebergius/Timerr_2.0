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
        'quantity_in_stock',
        'quantity_sold',
        'active',
        'parent_id',
        'type',
        'attributes'
    ];

    protected $casts = [
        'attributes' => 'array', // To handle JSON attributes as an array
        'type' => 'string', // Cast type to string
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

    // Self-referential relationship to parent product
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // Self-referential relationship to child products
    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }
}
