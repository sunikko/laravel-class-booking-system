<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassSession>
 */
class ClassSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startDate = now()->addDays(3)->startOfWeek();

        return [
            'class_name'    => $this->faker->words(2, true),
            'class_subject' => $this->faker->randomElement(['Math', 'English', 'Science']),
            'day_of_week'   => $this->faker->randomElement([
                'monday', 'tuesday', 'wednesday', 'thursday', 'friday'
            ]),
            'max_students'  => $this->faker->numberBetween(1, 5),
            'start_date'    => $startDate->toDateString(),
            'end_date'      => $startDate->copy()->addWeeks(4)->toDateString(),
            'start_time'    => '18:00',
            'duration_min'  => 60,

            'status'        => 'open',
        ];
    }

}
