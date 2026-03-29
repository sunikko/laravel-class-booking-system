<?php

namespace Database\Factories;

use App\Models\ClassSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Enums\BookingStatus;

class ClassSessionFactory extends Factory
{
    protected $model = ClassSession::class;

    public function definition(): array
    {
        $start = Carbon::instance(
            $this->faker->dateTimeBetween('+1 day', '+1 month')
        );


        $startTimes = [
            '10:00',
            '11:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
        ];

        return [
            'teacher_id' => 1,  // Temporarily set teacher_id to a dummy value
            'class_name' => $this->faker->randomElement(['Tom', 'Jake', 'Chris', 'Anna', 'Jane']) . "'s Class",
            'class_subject' => $this->faker->randomElement(['Math', 'English', 'Science']),

            'start_date' => $start,
            'end_date'   => $start->copy()->addWeeks(4),

            'day_of_week' => $start->isoWeekday(),

            'start_time' => $this->faker->randomElement($startTimes),
            'duration_min' => 60,
            'max_students' => $this->faker->numberBetween(3, 8),
            'status' => BookingStatus::CONFIRMED,
        ];
    }
}
