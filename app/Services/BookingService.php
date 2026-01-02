<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Student;

class BookingService
{
    public function submitBooking(
        Student $student,
        array $classSessionIds,
        string $date,
        ?string $comment
    ): Booking {
        return Booking::create([
            'student_id' => $student->id,
            'class_session_id' => $classSessionIds[0],
            'booking_date' => $date,
            'status' => 'confirmed',
        ]);
    }
}
