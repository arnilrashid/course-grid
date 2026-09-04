<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'type',
        'content',
        'position',
    ];

    /**
     * The section this lesson belongs to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Subtitle/caption files for this lesson, one per language.
     */
    public function subtitles(): HasMany
    {
        return $this->hasMany(Subtitle::class);
    }

    /**
     * Downloadable resources attached to this lesson.
     */
    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * Quizzes attached to this lesson.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
