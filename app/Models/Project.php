<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'invoice_status',
        'client_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function updateStatus()
    {
        // If the project is completed, do not change the status
        if ($this->status == 'completed') {
            return;
        }

        $today = now();
        if ($this->end_date) {
            $daysToEndDate = $today->diffInDays($this->end_date, false);
            if ($daysToEndDate < 0) {
                $this->status = 'overdue';
            } elseif ($daysToEndDate <= 3) { // Change this number to define what "nearing completion" means for you
                $this->status = 'nearing completion';
            } else {
                $this->status = 'ongoing';
            }
        } else {
            $this->status = 'ongoing';
        }
        $this->save();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
