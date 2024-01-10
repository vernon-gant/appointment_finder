<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for comments table. Used for comments on votes. Has own factory.
 */
class Comment extends Model
{
    use HasFactory;

    /**
     * Table name.
     * @var string
     */
    protected $table = 'comments';

    /**
     * Attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = [
        'vote_id',
        'content',
    ];

    /**
     * ONE-TO-ONE relationship with votes table.
     * One comment belongs to one vote.
     * @return BelongsTo
     */
    public function vote(): BelongsTo
    {
        return $this->belongsTo(Vote::class);
    }

}
