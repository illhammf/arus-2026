<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        $month = (string) $request->query('month', now()->format('Y-m'));
        [$start, $end] = $dashboard->monthBounds($month);

        $summary = $dashboard->summary($request->user(), $start, $end);

        return view('dashboard', [
            'summary' => $summary,
            'month' => $start->format('Y-m'),
            'monthLabel' => $start->translatedFormat('F Y'),
        ]);
    }
}
