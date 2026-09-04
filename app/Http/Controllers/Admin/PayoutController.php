<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\Admin\PayoutService;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function __construct(private PayoutService $payoutService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $this->payoutService->getPayouts(),
        ]);
    }

    public function markPaid(Payout $payout)
    {
        $this->payoutService->markPaid($payout);
        
        return back()->with('success', 'Payout marked as paid successfully.');
    }
}
