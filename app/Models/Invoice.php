<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'client_id',
        'title',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'subtotal',
        'discount',
        'vat',
        'total',
        'payment_terms',
        'payment_method',
        'transaction_id',
        'file_path',
        'last_reminder_sent',
    ];

    protected $dates = [
        'issue_date',
        'due_date',
        'last_reminder_sent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
