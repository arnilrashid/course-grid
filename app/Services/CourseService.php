<?php

namespace App\Services;

use App\Models\Course;
use App\Exceptions\CourseHasEnrollmentsException;

class CourseService
{
    /**
     * Delete a course.
     * 
     * @param Course $course
     * @return bool|null
     * @throws CourseHasEnrollmentsException
     */
    public function deleteCourse(Course $course)
    {
        if ($course->enrollments()->exists()) {
            throw new CourseHasEnrollmentsException();
        }

        return $course->delete();
    }

    /**
     * Get a list of published courses.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCourses()
    {
        return Course::where('status', \App\Enums\CourseStatus::PUBLISHED)
            ->with('instructor')
            ->get();
    }
}
