<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Appointment model
 */
class AppointmentFactory extends Factory {


	protected $model = Appointment::class;

	/**
	 * @inheritDoc
	 */
	public function definition() {
		return [
			'title' => $this->faker->sentence,
			'location' => $this->faker->city,
			'description' => $this->faker->paragraph,
			'expiration_date' => $this->faker->dateTimeBetween('-1 week', '+1 month'),
		];
	}
}