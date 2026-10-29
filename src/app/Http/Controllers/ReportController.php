<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Services\DashboardService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports, DashboardService $dashboard): View
    {
        $month = (string) $request->query('month', now()->format('Y-m'));
        [$start, $end] = $dashboard->monthBounds($month);
        $user = $request->user();

        return view('reports.index', [
            'month' => $start->format('Y-m'),
            'monthLabel' => $start->translatedFormat('F Y'),
            'summary' => $reports->summary($user, $start, $end),
            'trend' => $reports->monthlyTrend($user, $end),
            'expenseCategories' => $reports->categoryBreakdown($user, TransactionType::Expense, $start, $end),
            'incomeCategories' => $reports->categoryBreakdown($user, TransactionType::Income, $start, $end),
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function export(Request $request, ReportService $reports, DashboardService $dashboard): StreamedResponse
    {
        $month = (string) $request->query('month', now()->format('Y-m'));
        [$start, $end] = $dashboard->monthBounds($month);
        $rows = $reports->transactionRows($request->user(), $start, $end);

        $filename = 'arus-transaksi-' . $start->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Tanggal', 'Jenis', 'Akun', 'Kategori', 'Deskripsi', 'Nominal', 'Mata Uang', 'Catatan']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->occurred_at?->format('Y-m-d H:i'),
                    $row->type?->label(),
                    $row->account?->name,
                    $row->category?->name ?? '-',
                    $row->description,
                    $row->amount,
                    $row->currency,
                    $row->notes,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
