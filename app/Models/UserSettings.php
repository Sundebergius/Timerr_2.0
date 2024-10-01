<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'show_email',
        'show_address',
        'show_phone',
        'show_cvr',
        'show_city',
        'show_zip_code',
        'show_country',
        'show_notes',
        'show_contact_persons',
    ];

    // Define the inverse relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
