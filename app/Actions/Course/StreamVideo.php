<?php

namespace App\Actions\Course;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StreamVideo
{
    /**
     * Authorize and stream a lesson video.
     */
    public function __invoke(Course $course, Lesson $lesson): BinaryFileResponse|RedirectResponse
    {
        $this->ensureLessonBelongsToCourse($course, $lesson);
        $this->ensureVideoExists($lesson);
        $this->authorize($course, $lesson);

        return $this->stream($lesson);
    }

    private function ensureLessonBelongsToCourse(Course $course, Lesson $lesson): void
    {
        if ($lesson->section->course_id !== $course->id) {
            abort(404);
        }
    }

    private function ensureVideoExists(Lesson $lesson): void
    {
        if ($lesson->type !== 'video' || !$lesson->content) {
            abort(404, 'Video not found.');
        }
    }

    private function authorize(Course $course, Lesson $lesson): void
    {
        if ($lesson->is_free_preview) {
            return;
        }

        $user = request()->user();

        if (!$user) {
            abort(403, 'You must be logged in to view this video.');
        }

        $isEnrolled = $course->hasStudent($user);
        $isInstructor = $course->user_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (!$isEnrolled && !$isInstructor && !$isAdmin) {
            abort(403, 'You are not enrolled in this course.');
        }
    }

    private function stream(Lesson $lesson): BinaryFileResponse|RedirectResponse
    {
        $disk = Storage::disk('local');

        if ($disk->exists($lesson->content)) {
            return response()->file($disk->path($lesson->content));
        }

        if (str_starts_with($lesson->content, 'http')) {
            return redirect($lesson->content);
        }

        return response()->file(storage_path('app/' . $lesson->content));
    }
}
