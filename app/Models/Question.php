<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'options',
        'correct_answer',
        'explanation',
        'source',
    ];

    // Cast options to array/object automatically
    protected $casts = [
        'options' => 'array',
    ];
}