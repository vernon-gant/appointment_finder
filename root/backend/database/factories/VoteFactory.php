<?php

namespace Database\Factories;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for votes
 */
class VoteFactory extends Factory {

	protected $model = Vote::class;

	/**
	 * @inheritDoc
	 */
	public function definition(): array {
		return [
			'user_name' => $this->faker->name,
		];
	}
}