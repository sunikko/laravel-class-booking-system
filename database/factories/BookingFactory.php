<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Booking;
use App\Models\Student;
use App\Models\ClassSession;
use App\Enums\BookingStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_session_id' => ClassSession::factory(),
            'booking_date' => now()->toDateString(),
            'status' => BookingStatus::CONFIRMED,
        ];
    }
}
