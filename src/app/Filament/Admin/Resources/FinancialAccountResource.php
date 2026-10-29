<?php

namespace App\Filament\Admin\Resources;

use App\Enums\AccountType;
use App\Filament\Admin\Resources\FinancialAccountResource\Pages;
use App\Models\FinancialAccount;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialAccountResource extends Resource
{
    protected static ?string $model = FinancialAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $modelLabel = 'Akun Keuangan';

    protected static ?string $pluralModelLabel = 'Akun Keuangan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Akun')
                ->description('Akun keuangan milik pengguna.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pemilik')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Akun')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\Select::make('type')
                        ->label('Jenis')
                        ->options(AccountType::options())
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('currency')
                        ->label('Mata Uang')
                        ->default('IDR')
                        ->required()
                        ->maxLength(3),
                    Forms\Components\TextInput::make('opening_balance')
                        ->label('Saldo Awal')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->required(),
                    Forms\Components\DatePicker::make('opening_balance_date')
                        ->label('Tanggal Saldo Awal'),
                    Forms\Components\ColorPicker::make('color')
                        ->label('Warna'),
                    Forms\Components\Toggle::make('is_archived')
                        ->label('Diarsipkan'),
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
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable()
                    ->description(fn (FinancialAccount $record): string => $record->user?->name ?? '-'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (AccountType $state): string => $state->label())
                    ->color(fn (AccountType $state): string => $state->filamentColor())
                    ->sortable(),
                Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Saldo Awal')
                    ->formatStateUsing(fn ($state): string => Money::format($state))
                    ->alignEnd()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo Saat Ini')
                    ->state(fn (FinancialAccount $record): string => Money::format($record->balance()))
                    ->alignEnd()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_archived')
                    ->label('Diarsipkan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('Transaksi')
                    ->counts('transactions')
                    ->alignCenter()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
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
                    ->options(AccountType::options()),
                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label('Diarsipkan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialAccounts::route('/'),
            'create' => Pages\CreateFinancialAccount::route('/create'),
            'edit' => Pages\EditFinancialAccount::route('/{record}/edit'),
        ];
    }
}
