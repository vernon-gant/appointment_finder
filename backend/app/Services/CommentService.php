<?php

namespace App\Services;

use App\Exceptions\AppointmentNotFoundException;
use App\Exceptions\CommentNotDeletedException;
use App\Exceptions\CommentNotFoundException;
use App\Models\Appointment;
use App\Models\Comment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Service class for comments.
 * Performs all the business logic for comments and handles the database access.
 */
class CommentService
{

    /**
     * Returns all comments of the appointment with the given id.
     * @throws AppointmentNotFoundException
     */
    public function getCommentsOfAppointment(int $appointmentId): array
    {
        try {
            // Retrieve the appointment with the given id and eager load the appointment dates, votes and comments of this appointment
            $appointment = Appointment::with('appointmentDates.votes.comment')->findOrFail($appointmentId);
            $commentsWithUsernames = [];
            // Iterate over all votes and add all comments to the array
            foreach ($appointment->appointmentDates as $appointmentDate) {
                foreach ($appointmentDate->votes as $vote) {
                    if ($vote->comment) {
                        $commentsWithUsernames[] = [
                            'id' => $vote->comment->id,
                            'username' => $vote->user_name,
                            'comment' => $vote->comment->content,
                            'created_at' => $vote->comment->created_at->format('d.m.Y H:i:s'),
                        ];
                    }
                }
            }
            // Sort the comments by the latest
            usort($commentsWithUsernames, function ($a, $b) {
                return strcmp($b['created_at'], $a['created_at']);
            });

            return $commentsWithUsernames;
        } catch (ModelNotFoundException $e) {
            throw new AppointmentNotFoundException('Appointment with id ' . $appointmentId . ' not found.');
        }
    }


    /**
     * Deletes the comment with the given id.
     * @throws CommentNotDeletedException if the comment could not be deleted
     * @throws CommentNotFoundException if the comment with the given id does not exist
     */
    public function deleteComment(int $id): void
    {
        try {
            $comment = Comment::findOrFail($id);
            if (!$comment->delete()) {
                throw new CommentNotDeletedException('Comment with id ' . $id . ' could not be deleted.');
            }
        } catch (ModelNotFoundException $e) {
            throw new CommentNotFoundException('Comment with id ' . $id . ' not found.');
        }
    }

}