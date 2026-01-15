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
        $start = $this->faker->dateTimeBetween('+1 days', '+1 week');

        return [
            'max_students' => 10,
            'start_at' => $start,
            'end_at' => (clone $start)->modify('+1 hour'),
            'status' => 'open',
        ];
    }

}
