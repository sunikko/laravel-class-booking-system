<?php
/*
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Student;
use App\Models\ClassSession;
use App\Models\Booking;
use App\Enums\BookingStatus;

class BookingTransactionTest extends TestCase
{
    use RefreshDatabase;


    public function test_duplicate_active_booking_is_rejected_for_active_status(BookingStatus $status)
    {
        // Given: a student with an active booking
        $student = Student::factory()->create();
        $classSession = ClassSession::factory()->create();

        Booking::factory()->create([
            'student_id' => $student->id,
            'class_session_id' => $classSession->id,
            'status' => $status,
        ]);

        // When: the student tries to create another booking
        $response = $this->actingAs($student, 'api')
            ->postJson('/api/bookings', [
                'class_session_id' => $classSession->id,
                'date' => now()->toDateString(),
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
}

*/