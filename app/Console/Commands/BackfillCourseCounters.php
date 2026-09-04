<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('courses:backfill-counters')]
#[Description('Backfill average_rating, reviews_count, and enrollments_count for all existing courses')]
class BackfillCourseCounters extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backfill of course counters...');

        $count = 0;
        \App\Models\Course::chunkById(100, function ($courses) use (&$count) {
            foreach ($courses as $course) {
                \App\Jobs\RecalculateCourseCounters::dispatchSync($course->id);
                $count++;
            }
        });

        $this->info("Completed backfill for {$count} courses.");
    }
}
