<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Student;
use App\Models\ClassSession;
use App\Services\BookingService;
use App\Models\Booking;
use App\Enums\BookingStatus;

class BookingTransactionTest extends TestCase
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
    public function test_it_confirms_booking_when_capacity_is_available(): void
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

    /**
     * GIVEN a student already has an active booking (CONFIRMED or WAITING)
     * WHEN the student submits another booking request
     * THEN the request is rejected with HTTP 409
     * AND no new booking record is created
     */
    public function test_duplicate_active_booking_is_rejected_for_active_status()
    {
        // Given: a student with an active booking
        $user = User::factory()->create();
        $student = Student::factory()->create([
            'user_id' => $user->id,
        ]);
        $classSession = ClassSession::factory()->create();

        Booking::factory()->create([
            'student_id' => $student->id,
            'class_session_id' => $classSession->id,
            'status' => BookingStatus::CONFIRMED,
        ]);

        // When: the student tries to create another booking
        $response = $this->actingAs($user)
            ->postJson('/api/bookings', [
                'class_session_id' => $classSession->id,
                'booking_date' => now()->toDateString(),
            ]);

        // Then: booking is rejected
        $response->assertStatus(409)
            ->assertJson([
                'code' => 'ACTIVE_BOOKING_EXISTS',
            ]);

        // And: no additional booking is created
        $this->assertDatabaseCount('bookings', 1);
    }

    public static function activeBookingStatuses(): array
    {
        return [
            [BookingStatus::CONFIRMED],
            [BookingStatus::WAITING],
        ];
    }


    /**
    * Given
    * - class_session capacity = 1
    * - already confirmed booking 1 exists for the class_session
    * 
    * When
    * -  another student requests booking for the same class_session
    * 
    * Then
    * - booking is created
    * - status = WAITING
    */
    public function test_capacity_exceeded_results_in_waiting()
    {
        // Given
        $classSession = ClassSession::factory()->create([
            'max_students' => 1,
        ]);

        $existingStudent = Student::factory()->create();
        Booking::factory()->create([
            'student_id' => $existingStudent->id,
            'class_session_id' => $classSession->id,
            'status' => BookingStatus::CONFIRMED,
        ]);

        $newUser = User::factory()->create();
        $newStudent = Student::factory()->create([
            'user_id' => $newUser->id,
        ]);

        // When
        $response = $this->actingAs($newUser)
            ->postJson('/api/bookings', [
                'class_session_id' => $classSession->id,
                'booking_date' => now()->toDateString(),
            ]);

        // Then
        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'student_id' => $newStudent->id,
            'class_session_id' => $classSession->id,
            'status' => BookingStatus::WAITING,
        ]);
    }

}