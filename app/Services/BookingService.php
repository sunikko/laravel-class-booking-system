<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Student;
use App\Enums\BookingStatus;
use DomainException;
use Carbon\Carbon;

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
        if ($this->hasActiveBookingForSession($student, $classSessionId)) {
            throw new DomainException('ACTIVE_BOOKING_EXISTS', 3);
        }

        $classSession = ClassSession::findOrFail($classSessionId);

        $newStart = Carbon::parse($classSession->start_date)
            ->setTimeFromTimeString($classSession->start_time);

        $newEnd = $newStart->copy()->addMinutes($classSession->duration_min);

        if ($this->hasTimeConflict($student, $newStart, $newEnd)) {
            throw new DomainException('TIME_CONFLICT', 8);
        }

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
            'status' => BookingStatus::CONFIRMED,
        ]);
    }



    /**
     * Checks if a student has an active booking for a specific class session.
     *
     * @param Student $student The student to check.
     * @param int $classSessionId The ID of the class session.
     * @return bool True if an active booking exists, false otherwise.
     */
    public function hasActiveBookingForSession(
        Student $student,
        int $classSessionId
    ): bool {
        return Booking::where('student_id', $student->id)
            ->where('class_session_id', $classSessionId)
            ->whereIn('status', [
                BookingStatus::CONFIRMED,
                BookingStatus::WAITING,
            ])
            ->exists();
    }



    public function cancelBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking) { // lockForUpdate()
            $wasConfirmed = $booking->isConfirmed();

            $booking->cancel();

            if ($wasConfirmed) {
                $booking->classSession->promoteNextWaitingBooking();
            }
        }); //commit / rollback → auto unlock
    }

    public function hasTimeConflict(
        Student $student,
        Carbon $newStart,
        Carbon $newEnd
    ): bool {
        $bookings = Booking::with('classSession')
            ->where('student_id', $student->id)
            ->whereIn('status', [
                BookingStatus::CONFIRMED,
                BookingStatus::WAITING,
            ])
            ->get();

        foreach ($bookings as $booking) {
            $session = $booking->classSession;

            $existingStart = $session->startDateTime();
            $existingEnd   = $session->endDateTime();

            if (
                $existingStart < $newEnd &&
                $existingEnd > $newStart
            ) {
                return true;
            }
        }

        return false;
    }
}
