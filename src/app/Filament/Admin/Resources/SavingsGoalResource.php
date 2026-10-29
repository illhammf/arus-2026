<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GoalStatus;
use App\Filament\Admin\Resources\SavingsGoalResource\Pages;
use App\Filament\Admin\Resources\SavingsGoalResource\RelationManagers\ContributionsRelationManager;
use App\Models\FinancialAccount;
use App\Models\SavingsGoal;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SavingsGoalResource extends Resource
{
    protected static ?string $model = SavingsGoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Perencanaan';

    protected static ?string $modelLabel = 'Target Tabungan';

    protected static ?string $pluralModelLabel = 'Target Tabungan';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Target Tabungan')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pemilik')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('linked_account_id', null)),
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Target')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('target_amount')
                        ->label('Nominal Target')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    Forms\Components\DatePicker::make('target_date')
                        ->label('Tenggat'),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(GoalStatus::options())
                        ->default(GoalStatus::Active->value)
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('linked_account_id')
                        ->label('Akun Terkait')
                        ->options(fn (Get $get): array => FinancialAccount::query()
                            ->when($get('user_id'), fn ($query, $userId) => $query->where('user_id', $userId))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Target')
                    ->searchable()
                    ->sortable()
                    ->description(fn (SavingsGoal $record): string => $record->user?->name ?? '-'),
                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Target')
                    ->formatStateUsing(fn ($state): string => Money::format($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('contributed')
                    ->label('Terkumpul')
                    ->state(fn (SavingsGoal $record): string => Money::format($record->contributedTotal()))
                    ->alignEnd()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progres')
                    ->state(fn (SavingsGoal $record): string => number_format($record->progressPercentage(), 0) . '%')
                    ->badge()
                    ->color(fn (SavingsGoal $record): string => $record->isCompleted() ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('target_date')
                    ->label('Tenggat')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (GoalStatus $state): string => $state->label())
                    ->color(fn (GoalStatus $state): string => $state->filamentColor()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pemilik')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(GoalStatus::options()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('target_date');
    }

    public static function getRelations(): array
    {
        return [
            ContributionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSavingsGoals::route('/'),
            'create' => Pages\CreateSavingsGoal::route('/create'),
            'edit' => Pages\EditSavingsGoal::route('/{record}/edit'),
        ];
    }
}
