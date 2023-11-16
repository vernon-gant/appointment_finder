<?php

namespace App\Http\Controllers;

use App\Exceptions\AppointmentNotFoundException;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for comments. Has only two methods for getting all comments of an appointment and deleting a comment.
 * Has injected CommentService.
 */
class CommentController extends Controller {

    /**
     * Added to class with Dependency Injection
     * @var CommentService
     */
    protected CommentService $commentService;

	public function __construct(CommentService $commentService) {
		$this->commentService = $commentService;
	}

    /**
     * Get all comments of an appointment by appointment id.
     * 404 Not Found if appointment was not found.
     * 200 OK if comments were successfully found.
     * @param int $appointmentId
     * @return JsonResponse
     */
    public function index(int $appointmentId): JsonResponse {
		try {
			$comments = $this->commentService->getCommentsOfAppointment($appointmentId);
			return response()->json($comments);
		} catch (AppointmentNotFoundException $e) {
			return response()->json(['message' => $e->getMessage()], 404);
		}
	}

    /**
     * Delete a comment by id.
     * 500 Internal Server Error if comment was not deleted.
     * 200 OK if comment was successfully deleted.
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse {
		try {
			$this->commentService->deleteComment($id);
			return response()->json(['message' => 'Comment deleted successfully.']);
		} catch (\Exception $e) {
			return response()->json(['message' => $e->getMessage()], 500);
		}
	}

}