<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model for the appointments table. Used to store the appointments themselves. Has own factory.
 */
class Appointment extends Model
{
    use HasFactory;

    /**
     * Table name.
     * @var string
     */
    protected $table = 'appointments';

    /**
     * The attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = ['title', 'location', 'description', 'expiration_date'];

    /**
     * ONE-TO-MANY relationship with appointment_dates table.
     * One appointment can have many appointment dates.
     * @return HasMany
     */
    public function appointmentDates(): HasMany
    {
        return $this->hasMany(AppointmentDate::class);
    }
}
