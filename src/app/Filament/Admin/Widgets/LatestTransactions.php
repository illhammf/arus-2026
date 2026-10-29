<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use App\Support\Money;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with(['user', 'account'])
                    ->posted()
                    ->latest('occurred_at')
                    ->limit(8),
            )
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->description(fn (Transaction $record): string => $record->user?->name ?? '-'),
                Tables\Columns\TextColumn::make('account.name')
                    ->label('Akun'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state?->label() ?? '-'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state, Transaction $record): string => Money::format($state, $record->currency))
                    ->alignEnd()
                    ->weight('bold'),
            ])
            ->paginated(false);
    }
}
