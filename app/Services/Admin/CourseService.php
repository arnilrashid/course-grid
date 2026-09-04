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
     * Get paginated pending courses.
     */
    public function getPendingCourses(): LengthAwarePaginator
    {
        return Course::with('instructor')
            ->where('status', CourseStatus::IN_REVIEW)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

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
}
