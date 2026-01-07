<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatus;

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

    public function hasCapacity(): bool
    {
        $confirmedCount = $this->bookings()
            ->where('status', BookingStatus::CONFIRMED)
            ->count();

        return $confirmedCount < $this->max_students;
    }

}
