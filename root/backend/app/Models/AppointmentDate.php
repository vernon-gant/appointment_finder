<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model for appointment dates. Used to store the dates and times of the appointments. Has own factory.
 */
class AppointmentDate extends Model {
	use HasFactory;

    /**
     * Table name.
     * @var string
     */
    protected $table = 'appointment_dates';

    /**
     * Attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = ['date', 'start_time', 'end_time'];

    /**
     * ONE-TO-MANY relationship with votes table.
     * One appointment date can have many votes from users, which is the core idea of the application.
     * @return HasMany
     */
    public function votes(): HasMany {
		return $this->hasMany(Vote::class);
	}
}
