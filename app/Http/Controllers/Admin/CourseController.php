<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Admin\CourseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Courses/Index', [
            'courses' => $this->courseService->getPendingCourses(),
        ]);
    }

    public function approve(Course $course)
    {
        $this->courseService->approve($course);
        
        return back()->with('success', 'Course approved successfully.');
    }

    public function reject(Request $request, Course $course)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $this->courseService->reject($course, $request->input('reason'));
        
        return back()->with('success', 'Course rejected successfully.');
    }
}
