<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\Earning;
use App\Models\Order;
use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;
use App\Enums\CourseStatus;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get comprehensive KPIs for the dashboard overview.
     */
    public function getKpis(): array
    {
        $now = Carbon::now();
        $thirtyDaysAgo = (clone $now)->subDays(30);
        $sixtyDaysAgo = (clone $now)->subDays(60);

        // 1. Total Revenue (Current period vs Previous period)
        $currentRevenue = Order::where('created_at', '>=', $thirtyDaysAgo)->sum('total');
        $previousRevenue = Order::whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->sum('total');
        $totalRevenueAllTime = Order::sum('total');

        // 2. Total Orders
        $currentOrders = Order::where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousOrders = Order::whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $totalOrdersAllTime = Order::count();

        // 3. Students
        $currentStudents = User::role('student')->where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousStudents = User::role('student')->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $totalStudents = User::role('student')->count();

        // 4. Active Courses (Published)
        $currentCourses = Course::where('status', CourseStatus::PUBLISHED)->where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousCourses = Course::where('status', CourseStatus::PUBLISHED)->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $totalActiveCourses = Course::where('status', CourseStatus::PUBLISHED)->count();

        // 5. Active Instructors
        $currentInstructors = User::role('instructor')->where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousInstructors = User::role('instructor')->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $totalInstructors = User::role('instructor')->count();

        // 6. Active Subscriptions
        $currentSubscriptions = Subscription::where('status', 'active')->where('created_at', '>=', $thirtyDaysAgo)->count();
        $previousSubscriptions = Subscription::where('status', 'active')->whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $totalActiveSubscriptions = Subscription::where('status', 'active')->count();

        // Additional data for alerts/feed
        $pendingCoursesCount = Course::where('status', CourseStatus::IN_REVIEW)->count();
        $pendingRefundsCount = \App\Models\RefundRequest::where('status', 'pending')->count();
        $failedPaymentsCount = \App\Models\PaymentAttempt::where('status', 'failed')->where('created_at', '>=', $thirtyDaysAgo)->count();
        $pendingPayoutsCount = \App\Models\Payout::where('status', 'pending')->count();
        $failedJobsCount = DB::table('failed_jobs')->count();

        // Recent Activity Feed (Simplified for Phase 1: get latest 5 of a few key events)
        $activities = collect();
        
        $recentOrders = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get()->map(function ($order) {
            return ['type' => 'order', 'title' => 'New Order', 'description' => ($order->user?->name ?? 'A user') . ' purchased a course.', 'date' => $order->created_at];
        });
        
        $recentSignups = User::role('student')->orderBy('created_at', 'desc')->take(5)->get()->map(function ($user) {
            return ['type' => 'user', 'title' => 'New Student', 'description' => $user->name . ' joined the platform.', 'date' => $user->created_at];
        });
        
        $recentReviews = \App\Models\Review::with('user', 'course')->orderBy('created_at', 'desc')->take(5)->get()->map(function ($review) {
            return ['type' => 'review', 'title' => 'New Review', 'description' => ($review->user?->name ?? 'A user') . ' reviewed ' . ($review->course?->title ?? 'a deleted course'), 'date' => $review->created_at];
        });

        $activities = $activities->merge($recentOrders)->merge($recentSignups)->merge($recentReviews)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return [
            'revenue' => [
                'current' => $totalRevenueAllTime, // Show all-time on top, trend is 30d
                'period' => $currentRevenue,
                'trend' => $this->calculateTrend($currentRevenue, $previousRevenue)
            ],
            'orders' => [
                'current' => $totalOrdersAllTime,
                'period' => $currentOrders,
                'trend' => $this->calculateTrend($currentOrders, $previousOrders)
            ],
            'students' => [
                'current' => $totalStudents,
                'period' => $currentStudents,
                'trend' => $this->calculateTrend($currentStudents, $previousStudents)
            ],
            'courses' => [
                'current' => $totalActiveCourses,
                'period' => $currentCourses,
                'trend' => $this->calculateTrend($currentCourses, $previousCourses)
            ],
            'instructors' => [
                'current' => $totalInstructors,
                'period' => $currentInstructors,
                'trend' => $this->calculateTrend($currentInstructors, $previousInstructors)
            ],
            'subscriptions' => [
                'current' => $totalActiveSubscriptions,
                'period' => $currentSubscriptions,
                'trend' => $this->calculateTrend($currentSubscriptions, $previousSubscriptions)
            ],
            'alerts' => [
                'pending_courses' => $pendingCoursesCount,
                'pending_refunds' => $pendingRefundsCount,
                'failed_payments' => $failedPaymentsCount,
                'pending_payouts' => $pendingPayoutsCount,
                'failed_jobs' => $failedJobsCount,
            ],
            'activity' => $activities
        ];
    }

    private function calculateTrend(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
