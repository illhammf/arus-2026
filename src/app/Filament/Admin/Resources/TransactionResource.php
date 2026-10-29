<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Filament\Admin\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'description';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->posted()->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Transaksi')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pemilik')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('account_id', null)),
                    Forms\Components\Select::make('account_id')
                        ->label('Akun')
                        ->options(function (Get $get): array {
                            return FinancialAccount::query()
                                ->when($get('user_id'), fn ($query, $userId) => $query->where('user_id', $userId))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('type')
                        ->label('Jenis')
                        ->options(TransactionType::simpleOptions())
                        ->default(TransactionType::Expense->value)
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('category_id', null)),
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->options(function (Get $get): array {
                            return Category::query()
                                ->where('is_active', true)
                                ->where('type', $get('type'))
                                ->where(function ($query) use ($get) {
                                    $query->whereNull('user_id');

                                    if ($get('user_id')) {
                                        $query->orWhere('user_id', $get('user_id'));
                                    }
                                })
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->searchable(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(TransactionStatus::options())
                        ->default(TransactionStatus::Posted->value)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('amount')
                        ->label('Nominal')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                    Forms\Components\DateTimePicker::make('occurred_at')
                        ->label('Tanggal Transaksi')
                        ->seconds(false)
                        ->default(now())
                        ->required(),
                    Forms\Components\TextInput::make('description')
                        ->label('Deskripsi')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->visible(fn (?Transaction $record): bool => $record === null || ! $record->isTransfer()),
            Forms\Components\Section::make('Transfer')
                ->description('Transaksi transfer dikelola melalui halaman pengguna agar kedua sisi tetap konsisten.')
                ->schema([
                    Forms\Components\Placeholder::make('transfer_group_id')
                        ->label('ID Grup Transfer')
                        ->content(fn (?Transaction $record): string => $record?->transfer_group_id ?? '-'),
                ])
                ->visible(fn (?Transaction $record): bool => $record?->isTransfer() ?? false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(40)
                    ->description(fn (Transaction $record): string => $record->user?->name ?? '-'),
                Tables\Columns\TextColumn::make('account.name')
                    ->label('Akun')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (TransactionType $state): string => $state->label())
                    ->color(fn (TransactionType $state): string => $state->filamentColor()),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state, Transaction $record): string => Money::format($state, $record->currency))
                    ->color(fn (Transaction $record): string => in_array($record->type, [TransactionType::Income, TransactionType::TransferIn], true) ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (TransactionStatus $state): string => $state->label())
                    ->color(fn (TransactionStatus $state): string => $state->filamentColor()),
                Tables\Columns\TextColumn::make('transfer_group_id')
                    ->label('Grup Transfer')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pemilik')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(TransactionType::options()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(TransactionStatus::options()),
                Tables\Filters\Filter::make('occurred_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($query, $date) => $query->whereDate('occurred_at', '>=', $date))
                            ->when($data['until'], fn ($query, $date) => $query->whereDate('occurred_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Transaction $record): bool => ! $record->isTransfer()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Transaction $record): bool => ! $record->isTransfer()),
            ])
            ->defaultSort('occurred_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
