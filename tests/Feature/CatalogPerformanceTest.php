<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use App\Enums\EnrollmentStatus;
use App\Jobs\RecalculateCourseCounters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CatalogPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function createCourse(User $user)
    {
        return Course::create([
            'user_id' => $user->id,
            'title' => 'Test Course ' . uniqid(),
            'slug' => 'test-course-' . uniqid(),
            'price' => 10.00,
            'status' => \App\Enums\CourseStatus::PUBLISHED,
        ]);
    }

    public function test_creating_review_dispatches_recalculate_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $course = $this->createCourse($user);

        $review = Review::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'Great course!',
        ]);

        Queue::assertPushed(RecalculateCourseCounters::class, function ($job) use ($course) {
            return true;
        });
    }

    public function test_creating_enrollment_dispatches_recalculate_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $course = $this->createCourse($user);

        $enrollment = Enrollment::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'status' => EnrollmentStatus::ACTIVE,
        ]);

        Queue::assertPushed(RecalculateCourseCounters::class);
    }

    public function test_recalculate_job_computes_correct_counters()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $course = $this->createCourse($user1);

        // 2 Reviews
        Review::create(['course_id' => $course->id, 'user_id' => $user1->id, 'rating' => 4, 'comment' => 'A']);
        Review::create(['course_id' => $course->id, 'user_id' => $user2->id, 'rating' => 5, 'comment' => 'B']);

        // 3 Enrollments (1 active, 1 completed, 1 refunded)
        Enrollment::create(['course_id' => $course->id, 'user_id' => $user1->id, 'status' => EnrollmentStatus::ACTIVE]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $user2->id, 'status' => EnrollmentStatus::COMPLETED]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $user3->id, 'status' => EnrollmentStatus::REFUNDED]);

        // Run the job synchronously
        $job = new RecalculateCourseCounters($course->id);
        $job->handle();

        $course->refresh();

        $this->assertEquals(2, $course->reviews_count);
        $this->assertEquals(4.50, (float) $course->average_rating);
        $this->assertEquals(2, $course->enrollments_count); // The refunded one should not count
    }

    public function test_backfill_command_processes_all_courses()
    {
        $user = User::factory()->create();
        $course1 = $this->createCourse($user);
        $course2 = $this->createCourse($user); // e.g. drafted/archived
        
        Review::create(['course_id' => $course1->id, 'user_id' => $user->id, 'rating' => 5, 'comment' => 'A']);
        Enrollment::create(['course_id' => $course2->id, 'user_id' => $user->id, 'status' => EnrollmentStatus::ACTIVE]);
        
        // Reset counters just in case observers ran synchronously (though queue fake wouldn't have processed them, 
        // we use dispatchSync in command, so let's reset to 0 to be sure command actually does the work).
        $course1->updateQuietly(['reviews_count' => 0, 'average_rating' => null, 'enrollments_count' => 0]);
        $course2->updateQuietly(['reviews_count' => 0, 'average_rating' => null, 'enrollments_count' => 0]);

        $this->artisan('courses:backfill-counters')->assertSuccessful();

        $course1->refresh();
        $course2->refresh();

        $this->assertEquals(1, $course1->reviews_count);
        $this->assertEquals(5.00, (float) $course1->average_rating);
        
        $this->assertEquals(1, $course2->enrollments_count);
    }
}
