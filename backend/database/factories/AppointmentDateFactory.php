<?php

namespace Database\Factories;

use App\Models\AppointmentDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for AppointmentDate model
 */
class AppointmentDateFactory extends Factory
{
    protected $model = AppointmentDate::class;

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+1 week');
        $endTime = (clone $startTime)->modify('+1 hour');
        return [
            // Pick date from next week to next month
            'date' => $this->faker->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
        ];
    }
}
