<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'service_description',
        'start_date',
        'end_date',
        'total_amount',
        'currency',
        'due_date',
        'payment_terms',
        'status',
        'is_signed',
        'additional_terms'
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'due_date'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SIGNED = 'signed';
    const STATUS_COMPLETED = 'completed';

    public static function statuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_SIGNED,
            self::STATUS_COMPLETED,
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}