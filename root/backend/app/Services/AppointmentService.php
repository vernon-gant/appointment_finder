<?php

namespace App\Services;

use App\Exceptions\AppointmentDatesNotSavedException;
use App\Exceptions\AppointmentNotDeletedException;
use App\Exceptions\AppointmentNotFoundException;
use App\Exceptions\AppointmentNotSavedException;
use App\Models\Appointment;
use App\Models\AppointmentDate;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service class for appointments.
 * Contains all business logic for appointments.
 */
class AppointmentService
{

    /**
     * Returns appointments sorted by expiration date.
     * @return Collection
     */
    public function getAllAppointments(): Collection
    {
        // Return appointments sorted by expiration date
        return Appointment::orderBy('expiration_date', 'asc')->get();
    }

    /**
     * Get certain appointment by id.
     * @throws AppointmentNotFoundException
     */
    public function getAppointmentById(int $id): ?Appointment
    {
        try {
            return Appointment::findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new AppointmentNotFoundException('Appointment with id ' . $id . ' not found.');
        }
    }


    /**
     * Delete certain appointment by id.
     * @throws AppointmentNotDeletedException
     * @throws AppointmentNotFoundException
     */
    public function deleteAppointment(int $id): void
    {
        $appointment = $this->getAppointmentById($id);
        if (!$appointment->delete()) {
            throw new AppointmentNotDeletedException('Failed to delete the appointment with id ' . $id . '.');
        }
    }

    /**
     * Method responsible for creating an appointment using transaction.
     * First creates the appointment and then adds the appointment dates.
     * All changes are rolled back if an error occurs.
     * @throws AppointmentNotSavedException
     * @throws AppointmentDatesNotSavedException
     */
    public function createAppointment(array $data): Appointment
    {
        DB::beginTransaction();
        try {
            // Create the appointment
            $appointment = new Appointment([
                'title' => $data['title'],
                'location' => $data['location'],
                'description' => $data['description'],
                'expiration_date' => Carbon::parse($data['expiration_date']),
            ]);

            if (!$appointment->save()) {
                throw new AppointmentNotSavedException('Failed to save the appointment.');
            }

            $this->addAppointmentDates($appointment, $data);

            DB::commit();
            return $appointment;
        } catch (AppointmentNotSavedException|AppointmentDatesNotSavedException $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Called by createAppointment to add the appointment dates.
     * Uses appointment model to save the appointment dates.
     * @throws AppointmentDatesNotSavedException if the appointment dates could not be saved
     */
    private function addAppointmentDates(Appointment $appointment, array $data): void
    {
        $appointmentDates = [];

        foreach ($data['appointment_dates'] as $appointmentDate) {
            $appointmentDates[] = new AppointmentDate([
                'date' => Carbon::parse($appointmentDate['date']),
                'start_time' => $appointmentDate['start_time'],
                'end_time' => $appointmentDate['end_time'],
            ]);
        }

        // Save the appointment dates using hasMany relationship
        $result = $appointment->appointmentDates()->saveMany($appointmentDates);

        if (empty($result)) {
            throw new AppointmentDatesNotSavedException('Failed to save the appointment dates. All changes have been rolled back.');
        }
    }


}