<?php

namespace App\Services;

use App\Exceptions\AppointmentNotFoundException;
use App\Exceptions\VoteNotFoundException;
use App\Exceptions\VoteNotSavedException;
use App\Models\Comment;
use App\Models\Vote;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Service class for the vote model
 */
class VoteService
{

    /**
     * Retrieve all votes of an appointment by appointment id.
     * @param int $appointmentId
     * @return mixed
     * @throws AppointmentNotFoundException
     */
    public function getVotesOfAppointment(int $appointmentId): mixed
    {
        try {
            // Get all votes of the appointment using the appointment id and group them by user + sort them by user_name
            return Vote::join('appointment_dates', 'votes.appointment_date_id', '=', 'appointment_dates.id')
                ->where('appointment_dates.appointment_id', $appointmentId)
                ->select('votes.*')
                ->get()
                ->groupBy('user_name')
                ->sortBy(function ($vote, $userName) {
                    return $userName;
                })
                ->toArray();
        } catch (ModelNotFoundException) {
            throw new AppointmentNotFoundException('Appointment with id ' . $appointmentId . ' not found');
        }
    }

    /**
     * Retrieve a vote by id.
     * @param int $id
     * @return mixed
     * @throws VoteNotFoundException
     */
    public function getVoteById(int $id)
    {
        try {
            // Get the vote by id
            return Vote::findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new VoteNotFoundException('Vote with id ' . $id . ' not found');
        }
    }

    /**
     * Interface method for saving a vote.
     * Uses transaction to save vote and comment in one transaction.
     * @param array $data
     * @return array
     * @throws VoteNotSavedException
     */
    public function save(array $data): array
    {
        DB::beginTransaction();

        try {
            // For each appointment date id, create a vote and save it
            $votes = [];
            $comment = null;
            // Iterate over all appointment date ids
            foreach ($data['appointmentDateIds'] as $appointmentDateId) {
                $this->saveVote($appointmentDateId, $data['user_name'], $votes);
            }
            // If there is a comment, save it for the first vote to avoid duplicates
            $this->saveCommentIfPresent($data, $votes[0]);

            DB::commit();

            return [
                'votes' => $votes,
                'comment' => $comment
            ];
        } catch (VoteNotSavedException $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Helper method for saving a single vote.
     * @param mixed $appointmentDateId
     * @param string $user_name
     * @param array $votes
     * @return void
     * @throws VoteNotSavedException
     */
    public function saveVote(mixed $appointmentDateId, string $user_name, array &$votes): void
    {
        $vote = new Vote([
            'appointment_date_id' => (int)$appointmentDateId,
            'user_name' => $user_name,
        ]);
        if (!$vote->save()) {
            throw new VoteNotSavedException('Vote could not be saved');
        }
        $votes[] = $vote;
    }

    /**
     * Helper method for saving a comment.
     * @param array $data
     * @param Vote $vote
     * @throws VoteNotSavedException
     */
    public function saveCommentIfPresent(array $data, Vote $vote): void
    {
        // If there is a comment, save it
        if (isset($data['comment'])) {
            // Create a comment model and save it
            $comment = new Comment([
                'vote_id' => $vote->id,
                'content' => $data['comment'],
            ]);

            if (!$comment->save()) {
                throw new VoteNotSavedException('Comment could not be saved => all votes have been deleted');
            }
        }
    }

}