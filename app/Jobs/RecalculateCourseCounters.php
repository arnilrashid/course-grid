<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateCourseCounters implements ShouldQueue
{
    use Queueable;

    protected $courseId;

    /**
     * Create a new job instance.
     */
    public function __construct($courseId)
    {
        $this->courseId = $courseId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reviewsCount = \App\Models\Review::where('course_id', $this->courseId)->count();
        $averageRating = \App\Models\Review::where('course_id', $this->courseId)->avg('rating');
        
        $enrollmentsCount = \App\Models\Enrollment::where('course_id', $this->courseId)
            ->whereNotIn('status', [
                \App\Enums\EnrollmentStatus::CANCELLED,
                \App\Enums\EnrollmentStatus::REFUNDED,
            ])
            ->count();

        // Update the courses table directly to avoid triggering model events
        \Illuminate\Support\Facades\DB::table('courses')
            ->where('id', $this->courseId)
            ->update([
                'reviews_count' => $reviewsCount,
                'average_rating' => $averageRating ? round($averageRating, 2) : null,
                'enrollments_count' => $enrollmentsCount,
            ]);
    }
}
