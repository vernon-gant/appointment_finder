<?php

namespace App\Services;

use App\Exceptions\AppointmentDateNotFoundException;
use App\Exceptions\AppointmentNotFoundException;
use App\Models\Appointment;
use App\Models\AppointmentDate;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Service class for AppointmentDate model.
 * This class is used to handle all the business logic for the AppointmentDate model.
 */
class AppointmentDateService
{
    /**
     * Get all dates of an appointment.
     * @param int $id
     * @return array of AppointmentDate models
     * @throws AppointmentNotFoundException if appointment with id $id not found
     */
    public function getDatesOfAppointment(int $id): array
    {
        // Try to find the appointment, if model not found, throw exception
        try {
            $appointment = Appointment::findOrFail($id);
            return $appointment->appointmentDates()->get()->toArray();
        } catch (ModelNotFoundException $e) {
            throw new AppointmentNotFoundException("Appointment with id $id not found", 404, $e);
        }
    }

    /**
     * Fetch specific appointment date by id.
     * @param int $id
     * @return AppointmentDate
     * @throws AppointmentDateNotFoundException if appointment date with id $id not found
     */
    public function getAppointmentDate(int $id): AppointmentDate
    {
        try {
            return AppointmentDate::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new AppointmentDateNotFoundException("AppointmentDate with id $id not found", 404, $e);
        }
    }

}
