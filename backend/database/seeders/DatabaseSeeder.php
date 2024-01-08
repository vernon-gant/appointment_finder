<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentDate;
use App\Models\Comment;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 * @return void
	 */
	public function run(): void {
		// Create 10 user_names
		$userNames = collect(['Alice', 'Bob', 'Carol', 'David', 'Eve', 'Frank', 'Grace', 'Heidi', 'Ivan', 'Judy']);

		$appointments = Appointment::factory(10)->create();

		$appointments->each(function ($appointment) use ($userNames) {
            // Create 5 appointment dates for each appointment
			$appointmentDates = AppointmentDate::factory(5)->create(['appointment_id' => $appointment->id]);
			$usersWithComments = [];

			$appointmentDates->each(function ($appointmentDate) use ($usersWithComments, $userNames) {
                // Create 1-5 votes for each appointment date
				$selectedUserNames = $userNames->random(rand(1, 5));

				$selectedUserNames->each(function ($userName) use ($usersWithComments, $appointmentDate) {
					$vote = Vote::factory()->make(['user_name' => $userName, 'appointment_date_id' => $appointmentDate->id]);
					$appointmentDate->votes()->save($vote);

					// Add comment seeding with 50% chance
					if (rand(0, 1) === 1 && !in_array($userName, $usersWithComments)) {
						$comment = Comment::factory()->make(['vote_id' => $vote->id]);
						$vote->comment()->save($comment);
					}
				});
			});
		});

	}
}
