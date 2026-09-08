<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Course;
use App\Services\Admin\CourseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService)
    {
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/Courses/Index', [
            'courses' => $this->courseService->getAllCourses($request->only(['search', 'status'])),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Courses/Create', [
            'categories' => \App\Models\Category::all(),
            'instructors' => \App\Models\User::role('instructor')->get(['id', 'name', 'email']),
            'statuses' => \App\Enums\CourseStatus::cases(),
        ]);
    }

    public function store(StoreCourseRequest $request)
    {
        $this->courseService->createCourse($request->validated());

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['instructor', 'category', 'sections.lessons']);
        
        return Inertia::render('Admin/Courses/Show', [
            'course' => $course,
        ]);
    }

    public function edit(Course $course)
    {
        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
            'categories' => \App\Models\Category::all(),
            'instructors' => \App\Models\User::role('instructor')->get(['id', 'name', 'email']),
            'statuses' => \App\Enums\CourseStatus::cases(),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $this->courseService->updateCourse($course, $request->validated());

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $this->courseService->deleteCourse($course);

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
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
