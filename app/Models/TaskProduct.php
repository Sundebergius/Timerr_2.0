<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskProduct extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $fillable = [
        'task_id',
        'product_id',
        'type',  // Add this
        'quantity',  // Add this
        'attributes',  // Add this if you plan to mass-assign JSON data
        'total_price',  // Add this
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function registrationProducts()
    {
        return $this->hasMany(RegistrationProduct::class, 'task_product_id');
    }

    public function productMaterials()
    {
        return $this->product()->with('materials'); // Assuming you have a materials relationship on the Product model
    }

    public function calculateTotalMaterialQuantity()
    {
        $totalQuantity = 0;

        foreach ($this->product->materials as $material) {
            $totalQuantity += $material->quantity_in_stock; // Or use usage per unit if applicable
        }

        return $totalQuantity;
    }

    public function calculateTotalPrice()
    {
        $totalPrice = $this->product->price * $this->quantity;

        if ($this->type === 'service' && is_array($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $totalPrice += ($attribute['price'] ?? 0) * ($attribute['quantity'] ?? 1);
            }
        }

        return $totalPrice;
    }

    public function deleteWithRegistrations()
    {
        Log::info('deleteWithRegistrations called');

        try {
            DB::transaction(function () {
                $this->registrationProducts()->delete();
                $this->delete();
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete task and registrations: ' . $e->getMessage());
        }
    }
}
