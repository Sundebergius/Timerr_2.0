<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
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
        'attributes',
        'manage_inventory',
        'unit_type',           // New field
        'usage_per_unit',       // New field
        'is_material',          // New field
        'is_parent_material',
        'minimum_stock_alert',  // New field
        'cost_per_unit',        // Ensure cost_per_unit is fillable
        'price_per_unit',       // New field
    ];

    protected $casts = [
        'attributes' => 'array', // To handle JSON attributes as an array
        'type' => 'string', // Cast type to string
        'manage_inventory' => 'boolean', // Cast manage_inventory to boolean
        'usage_per_unit' => 'decimal:2', // Cast usage_per_unit to decimal with 2 decimal places
        'is_material' => 'boolean',      // Cast is_material to boolean
        'cost_per_unit' => 'decimal:2',  // Cast cost_per_unit to decimal with 2 decimal places
        'price_per_unit' => 'decimal:2', // Cast price_per_unit to decimal with 2 decimal places
        'is_parent_material' => 'boolean' // Cast is_parent_material to boolean
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

    public function materials()
    {
        return $this->hasMany(Product::class, 'parent_id')->where('type', 'material');
    }

    /**
     * Linked materials relationships for this product.
     */
    public function linkedMaterials()
    {
        return $this->hasMany(LinkedMaterial::class);
    }

    /**
     * Linked materials where this product is a parent material.
     */
    public function asParentMaterial1()
    {
        return $this->hasMany(LinkedMaterial::class, 'parent_material_1_id');
    }

    public function asParentMaterial2()
    {
        return $this->hasMany(LinkedMaterial::class, 'parent_material_2_id');
    }

}
