<?php

namespace App\Filament\Admin\Resources\SavingsGoalResource\RelationManagers;

use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'contributions';

    protected static ?string $title = 'Kontribusi';

    protected static ?string $modelLabel = 'Kontribusi';

    protected static ?string $pluralModelLabel = 'Kontribusi';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')
                ->label('Nominal')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\DateTimePicker::make('contributed_at')
                ->label('Tanggal Kontribusi')
                ->seconds(false)
                ->default(now())
                ->required(),
            Forms\Components\TextInput::make('notes')
                ->label('Catatan')
                ->maxLength(1000)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contributed_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state): string => Money::format($state))
                    ->weight('bold')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => [
                        ...$data,
                        'user_id' => $this->getOwnerRecord()->user_id,
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('contributed_at', 'desc');
    }
}
