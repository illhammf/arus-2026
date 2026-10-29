<?php

namespace App\Filament\Admin\Resources;

use App\Enums\BudgetPeriod;
use App\Filament\Admin\Resources\BudgetResource\Pages;
use App\Models\Budget;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationGroup = 'Perencanaan';

    protected static ?string $modelLabel = 'Anggaran';

    protected static ?string $pluralModelLabel = 'Anggaran';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Anggaran')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pemilik')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua pengeluaran'),
                    Forms\Components\Select::make('period_type')
                        ->label('Jenis Periode')
                        ->options(BudgetPeriod::options())
                        ->default(BudgetPeriod::Monthly->value)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('amount')
                        ->label('Batas Anggaran')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    Forms\Components\DatePicker::make('period_start')
                        ->label('Mulai Periode')
                        ->required(),
                    Forms\Components\DatePicker::make('period_end')
                        ->label('Akhir Periode')
                        ->required()
                        ->afterOrEqual('period_start'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder('Semua pengeluaran')
                    ->searchable()
                    ->description(fn (Budget $record): string => $record->user?->name ?? '-'),
                Tables\Columns\TextColumn::make('period_type')
                    ->label('Periode')
                    ->badge()
                    ->formatStateUsing(fn (BudgetPeriod $state): string => $state->label()),
                Tables\Columns\TextColumn::make('period_start')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_end')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Anggaran')
                    ->formatStateUsing(fn ($state): string => Money::format($state))
                    ->alignEnd()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('spent')
                    ->label('Terpakai')
                    ->state(fn (Budget $record): string => Money::format($record->spent()))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progres')
                    ->state(fn (Budget $record): string => number_format($record->usagePercentage(), 0) . '%')
                    ->badge()
                    ->color(fn (Budget $record): string => $record->isOverBudget() ? 'danger' : ($record->usagePercentage() >= 80 ? 'warning' : 'success')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pemilik')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('period_type')
                    ->label('Jenis Periode')
                    ->options(BudgetPeriod::options()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('period_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
