<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function monthlyTrend(User $user, Carbon $end, int $months = 6): array
    {
        $labels = [];
        $income = [];
        $expense = [];
        $net = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $end->copy()->subMonths($i)->startOfMonth();
            $finish = $start->copy()->endOfMonth();
            $in = $this->dashboard->sumFor($user, TransactionType::Income, $start, $finish);
            $out = $this->dashboard->sumFor($user, TransactionType::Expense, $start, $finish);

            $labels[] = $start->translatedFormat('M Y');
            $income[] = (float) $in;
            $expense[] = (float) $out;
            $net[] = (float) Money::sub($in, $out);
        }

        return compact('labels', 'income', 'expense', 'net');
    }

    public function categoryBreakdown(User $user, TransactionType $type, Carbon $start, Carbon $end): Collection
    {
        return $this->dashboard->categoryBreakdown($user, $type, $start, $end);
    }

    public function summary(User $user, Carbon $start, Carbon $end): array
    {
        return $this->dashboard->summary($user, $start, $end);
    }

    public function transactionRows(User $user, Carbon $start, Carbon $end): Collection
    {
        return $user->transactions()
            ->with(['account', 'category'])
            ->posted()
            ->whereBetween('occurred_at', [$start, $end])
            ->orderBy('occurred_at')
            ->get();
    }
}
