<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_question_id',
        'user_id',
        'body',
        'is_best_answer',
        'upvotes',
    ];

    protected $casts = [
        'is_best_answer' => 'boolean',
    ];

    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CourseQuestion::class, 'course_question_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
