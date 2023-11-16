<?php

namespace App\Http\Controllers;

use App\Exceptions\AppointmentNotFoundException;
use App\Exceptions\VoteNotFoundException;
use App\Exceptions\VoteNotSavedException;
use App\Rules\UniqueUserNameInAppointment;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Core controller for handling votes actions: create, get and get all.
 * Has injected VoteService for handling business logic.
 * Validates request data.
 */
class VoteController extends Controller
{

    /**
     * Added to class with Dependency Injection
     * @var VoteService
     */
    protected VoteService $voteService;

    public function __construct(VoteService $voteService)
    {
        $this->voteService = $voteService;
    }

    /**
     * Get all votes of an appointment by appointment id.
     * @param int $appointmentId
     * @return JsonResponse
     */
    public function index(int $appointmentId): JsonResponse
    {
        try {
            $votes = $this->voteService->getVotesOfAppointment($appointmentId);
            return response()->json($votes);
        } catch (AppointmentNotFoundException $e) {
            return response()->json(['errors' => $e->getMessage()], 404);
        }
    }

    /**
     * Create method for creating new vote. Validates request data.
     * Uses custom validation rule for checking if user name is unique in appointment.
     * 400 Bad Request if validation fails.
     * 201 Created if vote was successfully created.
     * 500 Internal Server Error if vote was not saved.
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_name' => ['required', 'string', 'max:255', new UniqueUserNameInAppointment($request->input('appointmentDateIds.0'))],
            'appointmentDateIds' => 'required|array',
            'appointmentDateIds.*' => 'required|integer|distinct|exists:appointment_dates,id',
            'comment' => 'nullable|string',
        ], [
            'user_name.required' => 'User name is required',
            'appointmentDateIds.required' => 'There must be at least one time slot selected',
            'appointmentDateIds.*.integer' => 'Each appointment date ID must be an integer',
            'appointmentDateIds.*.distinct' => 'Each appointment date ID must be unique',
            'appointmentDateIds.*.exists' => 'Appointment date ID not found',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        try {
            $vote = $this->voteService->save($request->all());
            return response()->json($vote);
        } catch (VoteNotSavedException $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }

    }

    /**
     * Get method for getting vote by id.
     * 404 Not Found if vote was not found.
     * 200 OK if vote was found.
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $vote = $this->voteService->getVoteById($id);
            return response()->json($vote);
        } catch (VoteNotFoundException $e) {
            return response()->json(['errors' => $e->getMessage()], 404);
        }
    }
}