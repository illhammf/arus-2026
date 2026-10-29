<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Arus 6 Bulan Terakhir';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        $labels = [];
        $income = [];
        $expense = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $labels[] = $start->translatedFormat('M Y');
            $income[] = (float) Transaction::query()
                ->posted()
                ->where('type', TransactionType::Income->value)
                ->whereBetween('occurred_at', [$start, $end])
                ->sum('amount');
            $expense[] = (float) Transaction::query()
                ->posted()
                ->where('type', TransactionType::Expense->value)
                ->whereBetween('occurred_at', [$start, $end])
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $income,
                    'backgroundColor' => '#14b8a6',
                    'borderColor' => '#0f766e',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expense,
                    'backgroundColor' => '#fb7185',
                    'borderColor' => '#e11d48',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
