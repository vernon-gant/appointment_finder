<?php

namespace App\Rules;

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

/**
 * Custom validation rule to check if the user_name is unique in the appointment.
 */
class UniqueUserNameInAppointment implements Rule
{
    /**
     * Used for storing appointment date id. Passed from controller over request for creating new vote.
     * @var string
     */
    protected string $appointmentDateId;

    public function __construct($appointmentDateId)
    {
        $this->appointmentDateId = $appointmentDateId;
    }

    /**
     * Check if given user_name is unique in the appointment.
     * For this we firstly need to get the appointment id from appointment date id.
     * Then we need to join votes table with appointment dates table and appointments table to get list of al votes of certain appointment.
     * Then we need to check if the user_name is already in the list.
     * @param $attribute - user_name
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Get appointment id from appointment date id.
        $appointmentId = DB::table('appointment_dates')
            ->select('appointment_id')
            ->where('id', $this->appointmentDateId)
            ->first()
            ->appointment_id;

        // Check if the user_name is already in the list.
        $query = DB::table('votes')
            ->select('votes.user_name')
            ->distinct()
            ->join('appointment_dates', 'votes.appointment_date_id', '=', 'appointment_dates.id')
            ->join('appointments', 'appointments.id', '=', 'appointment_dates.appointment_id')
            ->where('appointments.id', $appointmentId)
            ->where('votes.user_name', $value);

        return $query->doesntExist();
    }


    /**
     * Custom error message.
     * @return string
     */
    public function message(): string
    {
        return ':input has already voted in the appointment.';
    }
}
