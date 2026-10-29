<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class DashboardService
{
    public function __construct(
        private readonly FinancialAccountService $accountService,
    ) {}

    public function summary(User $user, Carbon $start, Carbon $end): array
    {
        $income = $this->sumFor($user, TransactionType::Income, $start, $end);
        $expense = $this->sumFor($user, TransactionType::Expense, $start, $end);

        $accounts = $user->financialAccounts()
            ->where('is_archived', false)
            ->orderBy('name')
            ->get();

        $balances = $this->accountService->balancesFor($user);

        return [
            'start' => $start,
            'end' => $end,
            'total_balance' => $this->accountService->totalBalance($user),
            'income' => $income,
            'expense' => $expense,
            'net' => Money::sub($income, $expense),
            'accounts' => $accounts,
            'account_balances' => $balances,
            'recent_transactions' => $user->transactions()
                ->with(['account', 'category'])
                ->posted()
                ->latest('occurred_at')
                ->latest('id')
                ->limit(8)
                ->get(),
            'expense_by_category' => $this->categoryBreakdown($user, TransactionType::Expense, $start, $end),
            'income_by_category' => $this->categoryBreakdown($user, TransactionType::Income, $start, $end),
            'transaction_count' => $user->transactions()
                ->posted()
                ->whereBetween('occurred_at', [$start, $end])
                ->count(),
            'daily_series' => $this->dailySeries($user, $start, $end),
            'budgets' => $this->budgetSummaries($user, $start, $end),
            'goals' => $user->savingsGoals()
                ->with('contributions')
                ->where('status', 'active')
                ->orderByRaw('target_date IS NULL')
                ->orderBy('target_date')
                ->limit(4)
                ->get(),
        ];
    }

    public function sumFor(User $user, TransactionType $type, Carbon $start, Carbon $end): string
    {
        return Money::normalize(
            $user->transactions()
                ->where('type', $type->value)
                ->posted()
                ->whereBetween('occurred_at', [$start, $end])
                ->sum('amount'),
        );
    }

    public function categoryBreakdown(User $user, TransactionType $type, Carbon $start, Carbon $end): Collection
    {
        return $user->transactions()
            ->where('transactions.type', $type->value)
            ->posted()
            ->whereBetween('occurred_at', [$start, $end])
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw("COALESCE(categories.name, 'Tanpa Kategori') as category_name")
            ->selectRaw("COALESCE(categories.color, '#64748b') as category_color")
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->category_name,
                'color' => $row->category_color,
                'total' => Money::normalize($row->total),
            ]);
    }

    public function dailySeries(User $user, Carbon $start, Carbon $end): array
    {
        $rows = $user->transactions()
            ->posted()
            ->whereIn('type', [TransactionType::Income->value, TransactionType::Expense->value])
            ->whereBetween('occurred_at', [$start, $end])
            ->selectRaw('DATE(occurred_at) as day, type, SUM(amount) as total')
            ->groupByRaw('DATE(occurred_at), type')
            ->get();

        $indexed = [];

        foreach ($rows as $row) {
            $type = $row->type instanceof TransactionType ? $row->type->value : (string) $row->type;
            $indexed[$row->day][$type] = Money::normalize($row->total);
        }

        $labels = [];
        $income = [];
        $expense = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('d');
            $income[] = (float) ($indexed[$key][TransactionType::Income->value] ?? 0);
            $expense[] = (float) ($indexed[$key][TransactionType::Expense->value] ?? 0);
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expense' => $expense,
        ];
    }

    public function budgetSummaries(User $user, Carbon $start, Carbon $end): Collection
    {
        return $user->budgets()
            ->with('category')
            ->where('period_start', '<=', $end->toDateString())
            ->where('period_end', '>=', $start->toDateString())
            ->orderBy('period_end')
            ->get()
            ->map(function (Budget $budget) {
                return [
                    'budget' => $budget,
                    'spent' => $budget->spent(),
                    'remaining' => $budget->remaining(),
                    'percentage' => $budget->usagePercentage(),
                    'over' => $budget->isOverBudget(),
                ];
            });
    }

    public function monthBounds(string $month): array
    {
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (Throwable) {
            $start = now()->startOfMonth();
        }

        return [$start, $start->copy()->endOfMonth()];
    }
}
