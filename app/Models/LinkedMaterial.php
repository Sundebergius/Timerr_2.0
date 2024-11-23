<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'parent_material_1_id',
        'parent_material_2_id',
        'child_material_relationships',
    ];

    protected $casts = [
        'child_material_relationships' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function parentMaterial1()
    {
        return $this->belongsTo(Material::class, 'parent_material_1_id');
    }

    public function parentMaterial2()
    {
        return $this->belongsTo(Material::class, 'parent_material_2_id');
    }
}
