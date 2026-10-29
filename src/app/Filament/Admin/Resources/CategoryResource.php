<?php

namespace App\Filament\Admin\Resources;

use App\Enums\CategoryType;
use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategori';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Kategori')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\Select::make('type')
                        ->label('Jenis')
                        ->options(CategoryType::options())
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('user_id')
                        ->label('Pemilik')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Kosongkan untuk kategori sistem yang tersedia bagi semua pengguna.'),
                    Forms\Components\Select::make('parent_id')
                        ->label('Kategori Induk')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('icon')
                        ->label('Ikon Heroicon')
                        ->placeholder('contoh: banknotes')
                        ->helperText('Nama ikon Heroicons tanpa prefix, misalnya banknotes, cake, truck.')
                        ->maxLength(50),
                    Forms\Components\ColorPicker::make('color')
                        ->label('Warna'),
                    Forms\Components\Toggle::make('is_system')
                        ->label('Kategori Sistem'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->icon(fn (Category $record): string => 'heroicon-o-' . ($record->icon ?: 'tag'))
                    ->color(fn (Category $record): string => 'gray'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (CategoryType $state): string => $state->label())
                    ->color(fn (CategoryType $state): string => $state->filamentColor()),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemilik')
                    ->placeholder('Sistem')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Induk')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_system')
                    ->label('Sistem')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('Transaksi')
                    ->counts('transactions')
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(CategoryType::options()),
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('Kategori Sistem'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
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
            ->defaultSort('type');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
