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
        'is_preview',
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

    /**
     * Determine if this lesson is a free preview.
     * True if explicitly marked, or if it is the first video lesson in the course.
     */
    public function getIsFreePreviewAttribute(): bool
    {
        if ($this->attributes['is_preview'] ?? false) {
            return true;
        }

        if (($this->attributes['type'] ?? '') !== 'video') {
            return false;
        }

        $this->loadMissing('section');

        if (!$this->section) {
            return false;
        }

        $firstVideoLesson = Lesson::whereHas('section', function ($q) {
                $q->where('course_id', $this->section->course_id);
            })
            ->where('type', 'video')
            ->join('sections', 'sections.id', '=', 'lessons.section_id')
            ->orderBy('sections.position')
            ->orderBy('lessons.position')
            ->select('lessons.*')
            ->first();

        return $firstVideoLesson && $firstVideoLesson->id === $this->id;
    }
}
