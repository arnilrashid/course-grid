<?php

namespace App\Http\Controllers;

use App\Actions\Course\StreamVideo;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\CourseService;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService)
    {
    }

    public function index()
    {
        $courses = $this->courseService->getCourses();

        return Inertia::render('Courses/Index', [
            'courses' => $courses
        ]);
    }

    public function show(Course $course)
    {
        $course->load(['instructor', 'category', 'sections.lessons']);

        // We also want to expose the 'is_free_preview' accessor for frontend
        $course->sections->each(function ($section) {
            $section->lessons->each(function ($lesson) {
                $lesson->append('is_free_preview');
            });
        });

        return Inertia::render('Courses/Show', [
            'course' => $course
        ]);
    }

    public function streamVideo(Course $course, Lesson $lesson, StreamVideo $action)
    {
        return $action($course, $lesson);
    }
}
