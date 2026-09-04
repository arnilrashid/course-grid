<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'kpis' => $this->dashboardService->getKpis(),
        ]);
    }
}
