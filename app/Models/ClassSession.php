<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookingStatus;
use Carbon\Carbon;

class ClassSession extends Model
{
    /** @use HasFactory<\Database\Factories\ClassSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_name',
        'class_subject',
        'start_date',
        'end_date',
        'day_of_week',
        'start_time',
        'duration_min',
        'max_students',
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

    public function waitingBookings()
    {
        return $this->bookings()
            ->where('status', BookingStatus::WAITING)
            ->orderBy('created_at');
    }

    public function promoteNextWaitingBooking(): void
    {
        $next = $this->waitingBookings()
            ->lockForUpdate()
            ->first();

        if (! $next) {
            return;
        }

        $next->confirm();
    }

    public function startDateTime(): Carbon
    {
        return Carbon::parse($this->start_date)
            ->setTimeFromTimeString($this->start_time);
    }

    public function endDateTime(): Carbon
    {
        return $this->startDateTime()
            ->copy()
            ->addMinutes($this->duration_min);
    }
}
