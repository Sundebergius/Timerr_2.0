<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_product_id',
        'product_id',
        'quantity',
    ];

    public function taskProduct()
    {
        return $this->belongsTo(TaskProduct::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
