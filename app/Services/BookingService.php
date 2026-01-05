<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Student;
use App\Enums\BookingStatus;
use DomainException;

class BookingService
{
    /**
     * Creates a new booking for a student if no active booking exists.
     *
     * @param Student $student The student making the booking.
     * @param int $classSessionId The ID of the class session to book.
     * @param string $date The date of the booking.
     *
     * @throws \DomainException if an active booking already exists for the student.
     *
     * @return void
     */
    public function createBooking(Student $student, int $classSessionId, string $date): void
    {
        $hasActiveBooking = Booking::where('student_id', $student->id)
            ->whereIn('status', [
                BookingStatus::CONFIRMED,
                BookingStatus::WAITING,
            ])
            ->exists();

        if ($hasActiveBooking) {
            throw new DomainException('ACTIVE_BOOKING_EXISTS');
        }

        $classSession = ClassSession::findOrFail($classSessionId);

        $status = $classSession->hasCapacity()
        ? BookingStatus::CONFIRMED
        : BookingStatus::WAITING;

        Booking::create([
            'student_id' => $student->id,
            'class_session_id' => $classSessionId,
            'booking_date' => $date,
            'status' => $status,
        ]);
    }

    /**
     * Submits a booking for a student for given class sessions on a specific date.
     *
     * @param Student $student The student making the booking.
     * @param array $classSessionIds Array of class session IDs to book.
     * @param string $date The date of the booking.
     * @param string|null $comment Optional comment for the booking.
     * @return Booking The created booking instance.
     */
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
