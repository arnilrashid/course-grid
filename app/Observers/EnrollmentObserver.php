<?php

namespace App\Observers;

use App\Models\Enrollment;

class EnrollmentObserver
{
    /**
     * Handle the Enrollment "created" event.
     */
    public function created(Enrollment $enrollment): void
    {
        \App\Jobs\RecalculateCourseCounters::dispatch($enrollment->course_id);
    }

    /**
     * Handle the Enrollment "updated" event.
     */
    public function updated(Enrollment $enrollment): void
    {
        if ($enrollment->isDirty('status')) {
            \App\Jobs\RecalculateCourseCounters::dispatch($enrollment->course_id);
        }
    }

    /**
     * Handle the Enrollment "deleted" event.
     */
    public function deleted(Enrollment $enrollment): void
    {
        \App\Jobs\RecalculateCourseCounters::dispatch($enrollment->course_id);
    }

    /**
     * Handle the Enrollment "restored" event.
     */
    public function restored(Enrollment $enrollment): void
    {
        //
    }

    /**
     * Handle the Enrollment "force deleted" event.
     */
    public function forceDeleted(Enrollment $enrollment): void
    {
        //
    }
}
