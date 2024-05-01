<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'invoice_status',
        'client_id',
    ];

    public function updateStatus()
    {
        $today = now();

        if ($this->end_date) {
            $daysToEndDate = $today->diffInDays($this->end_date, false);

            if ($daysToEndDate < 0) {
                $this->status = 'overdue';
            } elseif ($daysToEndDate <= 7) { // Change this number to define what "nearing completion" means for you
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
}
