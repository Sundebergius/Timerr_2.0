<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'webhooks';

    // Define which attributes are mass assignable
    protected $fillable = ['user_id', 'url', 'name', 'event', 'active'];

    // Define the relationship to the User model (if applicable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
