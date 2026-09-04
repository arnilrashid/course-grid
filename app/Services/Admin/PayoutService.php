<?php

namespace App\Services\Admin;

use App\Models\Payout;
use App\Enums\PayoutStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Admin\AuditLogger;

class PayoutService
{
    /**
     * Get paginated payouts.
     */
    public function getPayouts(): LengthAwarePaginator
    {
        return Payout::with(['instructor', 'earnings'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Mark a payout as paid.
     */
    public function markPaid(Payout $payout): void
    {
        $oldValues = $payout->only(['status', 'paid_at']);

        $payout->update([
            'status' => PayoutStatus::PAID,
            'paid_at' => now(),
        ]);

        AuditLogger::log('mark_payout_paid', $payout, $oldValues, $payout->only(['status', 'paid_at']));
    }
}
