<?php

namespace App\Services;

use App\Models\Earning;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class EarningService
{
    /**
     * Resolve the effective revenue share percentage for an instructor on a course.
     *
     * Priority: course_instructors override → settings table default → hardcoded fallback.
     */
    public function resolveRevenueSharePercentage(int $courseId, int $instructorId): float
    {
        // 1. Check course_instructors for a per-course/instructor override
        $override = DB::table('course_instructors')
            ->where('course_id', $courseId)
            ->where('instructor_id', $instructorId)
            ->value('revenue_share_percentage');

        if ($override !== null) {
            return (float) $override;
        }

        // 2. Fall back to the platform-wide default in settings
        $default = DB::table('settings')
            ->where('key', 'default_revenue_share_percentage')
            ->value('value');

        if ($default !== null) {
            return (float) $default;
        }

        // 3. Hardcoded fallback only if settings row is somehow missing
        return 70.00;
    }

    /**
     * Create an earning idempotently using source_key.
     *
     * On duplicate-key, fetches and returns the existing row instead of throwing.
     *
     * @param array $data Must include 'source_key'
     * @return Earning
     * @throws Exception
     */
    public function createEarningIdempotently(array $data): Earning
    {
        return DB::transaction(function () use ($data) {
            try {
                return Earning::firstOrCreate(
                    ['source_key' => $data['source_key']],
                    $data
                );
            } catch (QueryException $e) {
                // Check if it's a unique constraint violation (MySQL code 1062 / SQLSTATE 23000)
                if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                    return Earning::where('source_key', $data['source_key'])->firstOrFail();
                }

                throw $e;
            }
        });
    }
}

