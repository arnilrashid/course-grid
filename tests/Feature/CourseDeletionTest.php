<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Certificate;
use App\Services\CourseService;
use App\Exceptions\CourseHasEnrollmentsException;

class CourseDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_soft_deletes_and_keeps_enrollments()
    {
        $user = User::create([
            'name' => 'Instructor',
            'email' => 'instructor@example.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $course = Course::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Test Course',
            'slug' => 'test-course',
            'description' => 'A test course',
            'language' => 'en',
            'price' => 10.00,
            'status' => 'published',
        ]);

        $student = User::create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'provenance' => 'purchase'
        ]);

        $certificate = Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_number' => 'CERT123',
            'issued_at' => now(),
        ]);

        $service = new CourseService();

        try {
            $service->deleteCourse($course);
            $this->fail('Expected CourseHasEnrollmentsException was not thrown.');
        } catch (CourseHasEnrollmentsException $e) {
            $this->assertEquals('course_has_enrollments', $e->render(request())->getData()->error);
        }

        // Test fallback: even if we forcefully soft delete, constraints should be fine
        $course->delete();

        $this->assertSoftDeleted('courses', [
            'id' => $course->id
        ]);

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'course_id' => $course->id
        ]);

        $this->assertDatabaseHas('certificates', [
            'id' => $certificate->id,
            'course_id' => $course->id
        ]);
    }
    public function test_can_reuse_slug_after_soft_delete()
    {
        $user = User::create([
            'name' => 'Instructor',
            'email' => 'instructor2@example.com',
            'password' => bcrypt('password'),
        ]);

        $course1 = Course::create([
            'user_id' => $user->id,
            'title' => 'First Course',
            'slug' => 'reusable-slug',
            'price' => 10.00,
        ]);

        $course1->delete();

        $course2 = Course::create([
            'user_id' => $user->id,
            'title' => 'Second Course',
            'slug' => 'reusable-slug',
            'price' => 10.00,
        ]);

        $this->assertEquals('reusable-slug', $course2->slug);
        $this->assertNotEquals($course1->id, $course2->id);
    }

    public function test_cannot_reuse_slug_for_active_courses()
    {
        $user = User::create([
            'name' => 'Instructor',
            'email' => 'instructor3@example.com',
            'password' => bcrypt('password'),
        ]);

        Course::create([
            'user_id' => $user->id,
            'title' => 'First Course',
            'slug' => 'active-slug',
            'price' => 10.00,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionCode('23000'); // Integrity constraint violation

        Course::create([
            'user_id' => $user->id,
            'title' => 'Second Course',
            'slug' => 'active-slug',
            'price' => 10.00,
        ]);
    }
}
