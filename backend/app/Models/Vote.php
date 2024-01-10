<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model for representing votes. Used for storing votes in the database. Has its own factory.
 */
class Vote extends Model
{
    use HasFactory;

    /**
     * Table name in the database.
     * @var string
     */
    protected $table = 'votes';

    /**
     * Attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = ['user_name', 'appointment_date_id'];


    /**
     * MANY-TO-ONE relationship with appointment_dates table.
     * Many votes can belong to one appointment date.
     * @return BelongsTo
     */
    public function appointmentDate(): BelongsTo
    {
        return $this->belongsTo(AppointmentDate::class);
    }

    /**
     * ONE-TO-ONE relationship with comments table.
     * One vote can have one comment.
     * @return HasOne
     */
    public function comment(): HasOne
    {
        return $this->hasOne(Comment::class);
    }
}
