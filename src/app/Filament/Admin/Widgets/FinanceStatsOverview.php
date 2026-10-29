<?php

namespace App\Filament\Admin\Widgets;

use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $openingBalance = FinancialAccount::query()->sum('opening_balance');
        $movement = Transaction::query()
            ->posted()
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('income', 'transfer_in') THEN amount ELSE -amount END), 0) as total")
            ->value('total');

        return [
            Stat::make('Pengguna', number_format(User::query()->count(), 0, ',', '.'))
                ->description(User::query()->where('status', 'active')->count() . ' akun aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Akun Keuangan', number_format(FinancialAccount::query()->count(), 0, ',', '.'))
                ->description(FinancialAccount::query()->where('is_archived', false)->count() . ' akun aktif')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('info'),
            Stat::make('Transaksi Tercatat', number_format(Transaction::query()->posted()->count(), 0, ',', '.'))
                ->description('Seluruh pengguna')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('warning'),
            Stat::make('Total Saldo Pengguna', Money::format(Money::add($openingBalance, $movement)))
                ->description('Saldo awal + seluruh transaksi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
