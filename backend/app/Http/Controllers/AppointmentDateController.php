<?php

namespace App\Http\Controllers;

use App\Exceptions\AppointmentDateNotFoundException;
use App\Exceptions\AppointmentNotFoundException;
use App\Services\AppointmentDateService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for appointment dates. Only two methods are implemented, because the other methods are not needed.
 * Get all appointment dates of an appointment and get a specific appointment date.
 * Has injected AppointmentDateService.
 */
class AppointmentDateController extends Controller {

    /**
     * Added to class with Dependency Injection
     * @var AppointmentDateService
     */
    protected AppointmentDateService $appointmentDatesService;

	public function __construct(AppointmentDateService $appointmentDatesService) {
		$this->appointmentDatesService = $appointmentDatesService;
	}

    /**
     * Get all appointment dates of an appointment by appointment id.
     * 404 Not Found if appointment was not found.
     * 200 OK if appointment dates were successfully found.
     * @param int $appointmentId
     * @return JsonResponse
     */
    public function index(int $appointmentId): JsonResponse {
		try {
			$appointmentDates = $this->appointmentDatesService->getDatesOfAppointment($appointmentId);
			return response()->json($appointmentDates);
		} catch (AppointmentNotFoundException $e) {
			return response()->json(['error' => $e->getMessage()], 404);
		}
	}

    /**
     * Just utility method for getting a specific appointment date by id.
     * 404 Not Found if appointment date was not found.
     * 200 OK if appointment date was successfully found.
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse {
		try {
			$appointmentDate = $this->appointmentDatesService->getAppointmentDate($id);
			return response()->json($appointmentDate);
		} catch (AppointmentDateNotFoundException $e) {
			return response()->json(['error' => $e->getMessage()], 404);
		}
	}
}