<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    use HasFactory;

    protected $table = 'contact_persons';

    protected $fillable = [
        'client_id',   // Add client_id here
        'name',
        'email',
        'phone',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
