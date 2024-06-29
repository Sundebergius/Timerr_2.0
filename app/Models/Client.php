<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'email', 'cvr', 'phone', 'address', 'country', 'status'];

    // protected $casts = [
    //     'tag_colors' => 'array',
    // ];

    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_COMPANY = 'company';

    const STATUS_LEAD = 'lead';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_INTERESTED = 'interested';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_DEAL_MADE = 'deal_made';

    public static function types()
    {
        return [
            self::TYPE_INDIVIDUAL,
            self::TYPE_COMPANY,
        ];
    }

    public static function statuses()
    {
        return [
            self::STATUS_LEAD,
            self::STATUS_CONTACTED,
            self::STATUS_INTERESTED,
            self::STATUS_NEGOTIATION,
            self::STATUS_DEAL_MADE,
        ];
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function clientNote()
    {
        return $this->hasOne(clientNote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
