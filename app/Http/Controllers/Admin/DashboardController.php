<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reports
    ) {}

    public function index(): View
    {
        $stats = $this->reports->dashboardStats(request()->only(['date_from', 'date_to']));

        return view('admin.dashboard.index', compact('stats'));
    }
}
