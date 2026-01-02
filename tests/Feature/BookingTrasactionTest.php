<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingTrasactionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Booking Transaction – Happy Path
     *
     * Purpose:
     * When a student submits a booking for class sessions
     * that still have available capacity,
     * the system should create the booking with `confirmed` status.
     *
     * This test verifies that:
     * - Booking logic is handled at the service layer
     * - A booking is marked as confirmed when capacity is not exceeded
     */
    public function it_comfirms_booking_when_capacity_is_available(): void
    {
        // given: a student
        $student = Student::factory()->create();

        // and: a clas session with available capacity
        $classSession = ClassSession::factory()->create(['max_students' => 2]);

        // when: the student submits a booking request
        $service = app(BookingService::class);

        $booking = $service->submitBooking(
            student: $student,
            classSessionIds: [$classSession->id],
            date: '2026-01-10',
            comment: null
        );

        // then: booking is confirmed
        $this->assertEquals('confirmed', $booking->status);
    }
}