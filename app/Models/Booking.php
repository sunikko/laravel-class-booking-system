<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\BookingStatus;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_session_id',
        'booking_date',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function confirm(): void
    {
        $this->update([
            'status' => BookingStatus::CONFIRMED,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => BookingStatus::CANCELLED,
        ]);
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::CONFIRMED;
    }

}
