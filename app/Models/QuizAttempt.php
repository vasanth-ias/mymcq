<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'score',
        'total_questions',
        'attempt_details',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'attempt_details' => 'array', // Cast JSON column to array
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the QuizAttempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}