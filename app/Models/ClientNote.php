<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientNote extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'content'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
