<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Enums\CourseStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Admin\AuditLogger;
use Illuminate\Support\Facades\Auth;

class CourseService
{
    /**
     * Approve a course.
     */
    public function approve(Course $course): void
    {
        $oldValues = $course->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']);

        $course->update([
            'status' => CourseStatus::PUBLISHED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        AuditLogger::log('approve_course', $course, $oldValues, $course->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']));
    }

    /**
     * Reject a course.
     */
    public function reject(Course $course, string $reason): void
    {
        $oldValues = $course->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']);

        $course->update([
            'status' => CourseStatus::REJECTED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        AuditLogger::log('reject_course', $course, $oldValues, $course->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']));
    }

    /**
     * Get paginated list of all courses with filters.
     */
    public function getAllCourses(array $filters = []): LengthAwarePaginator
    {
        $query = Course::with(['instructor', 'category']);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Create a new course.
     */
    public function createCourse(array $data): Course
    {
        $data['status'] = $data['status'] ?? CourseStatus::DRAFT;
        
        $course = Course::create($data);

        AuditLogger::log('create_course', $course, [], $course->toArray());

        return $course;
    }

    /**
     * Update an existing course.
     */
    public function updateCourse(Course $course, array $data): Course
    {
        $oldValues = $course->toArray();

        $course->update($data);

        AuditLogger::log('update_course', $course, $oldValues, $course->toArray());

        return $course;
    }

    /**
     * Delete a course.
     */
    public function deleteCourse(Course $course): void
    {
        $oldValues = $course->toArray();

        $course->delete();

        AuditLogger::log('delete_course', $course, $oldValues, []);
    }
}
