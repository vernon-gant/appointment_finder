<?php

namespace App\Http\Controllers;

use App\Exceptions\AppointmentDatesNotSavedException;
use App\Exceptions\AppointmentNotDeletedException;
use App\Exceptions\AppointmentNotFoundException;
use App\Exceptions\AppointmentNotSavedException;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for handling appointments actions: create, get, delete, get all.
 * Has injected AppointmentService for handling business logic.
 * Validates request data.
 */
class AppointmentController extends Controller
{
    /**
     * Added to class with Dependency Injection
     * @var AppointmentService
     */
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Index method for getting all appointments.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $appointments = $this->appointmentService->getAllAppointments();
        return response()->json($appointments);
    }

    /**
     * Create method for creating new appointment. Validates request data.
     * 400 Bad Request if validation fails.
     * 201 Created if appointment was successfully created.
     * 500 Internal Server Error if appointment or appointment dates were not saved.
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        // Validation rules
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|string',
                'location' => 'required|string',
                'description' => 'required|string',
                'expiration_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
                'appointment_dates' => 'required|array|min:1',
                'appointment_dates.*.date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
                'appointment_dates.*.start_time' => 'required|date_format:H:i',
                'appointment_dates.*.end_time' => 'required|date_format:H:i',],
            ['title.required' => 'Title is required',
                'location.required' => 'Location is required',
                'description.required' => 'Description is required',
                'expiration_date.required' => 'Expiration date is required',
                'appointment_dates.required' => 'Appointment dates are required',
                'appointment_dates.min' => 'There must be at least one appointment date',
                'appointment_dates.*.date.required' => 'Date is required',
                'appointment_dates.*.date.after_or_equal' => 'Date must be equal or after today',
                'appointment_dates.*.date.before' => 'Date must be before the expiration date',
                'appointment_dates.*.start_time.required' => 'Start time is required',
                'appointment_dates.*.end_time.required' => 'End time is required',]
        );
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        try {
            $appointment = $this->appointmentService->createAppointment($request->all());
            return response()->json($appointment, 201);
        } catch (AppointmentNotSavedException|AppointmentDatesNotSavedException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get method for getting appointment by id.
     * 404 Not Found if appointment was not found.
     * 200 OK if appointment was successfully found.
     * @param int $appointmentId
     * @return JsonResponse
     */
    public function get(int $appointmentId): JsonResponse
    {
        try {
            $appointment = $this->appointmentService->getAppointmentById($appointmentId);
            return response()->json($appointment);
        } catch (AppointmentNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Delete method for deleting appointment by id.
     * 404 Not Found if appointment was not found.
     * 200 OK if appointment was successfully deleted.
     * 500 Internal Server Error if appointment was not deleted.
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        try {
            $this->appointmentService->deleteAppointment($id);
            return response()->json(['message' => 'Appointment was successfully deleted.']);
        } catch (AppointmentNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (AppointmentNotDeletedException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
