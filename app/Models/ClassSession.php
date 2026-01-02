<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    /** @use HasFactory<\Database\Factories\ClassSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'max_students',
        'start_at',
        'end_at',
        'status',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
