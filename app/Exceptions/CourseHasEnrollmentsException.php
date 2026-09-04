<?php

namespace App\Exceptions;

use Exception;

class CourseHasEnrollmentsException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): bool
    {
        return false;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        // For Inertia or API requests we should usually return a standard response or let the handler format it.
        // Returning a generic exception is fine.
        return response()->json([
            'message' => 'Cannot delete course with active enrollments.',
            'error' => 'course_has_enrollments'
        ], 403);
    }
}
