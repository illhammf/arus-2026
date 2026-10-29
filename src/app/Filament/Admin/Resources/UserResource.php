<?php

namespace App\Filament\Admin\Resources;

use App\Enums\UserStatus;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'roles.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Peran' => $record->roles->pluck('name')->implode(', '),
            'HP' => PhoneNormalizer::display($record->phone),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Profil Pengguna')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->minLength(2)
                        ->maxLength(255)
                        ->required(),
                    Forms\Components\TextInput::make('phone')
                        ->label('Nomor HP')
                        ->tel()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn ($state) => PhoneNormalizer::normalize($state))
                        ->rule(fn (): Closure => function (string $attribute, $value, Closure $fail): void {
                            if (filled($value) && PhoneNormalizer::normalize($value) === null) {
                                $fail('Nomor HP tidak valid. Gunakan format 0812… atau +62812….');
                            }
                        })
                        ->helperText('Nomor HP menjadi identitas login pengguna biasa.'),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Opsional untuk pengguna yang masuk dengan nomor HP.'),
                    Forms\Components\FileUpload::make('avatar_url')
                        ->label('Avatar')
                        ->image()
                        ->imageEditor()
                        ->imagePreviewHeight('180')
                        ->panelLayout('integrated')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Akun & Keamanan')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->confirmed()
                        ->dehydrated(fn ($state): bool => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->helperText('Kosongkan saat mengedit jika tidak ingin mengubah password.'),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Ulangi Password')
                        ->password()
                        ->dehydrated(false)
                        ->required(fn (string $context): bool => $context === 'create'),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(UserStatus::options())
                        ->default(UserStatus::Active->value)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('currency')
                        ->label('Mata Uang')
                        ->default('IDR')
                        ->maxLength(3),
                    Forms\Components\TextInput::make('timezone')
                        ->label('Zona Waktu')
                        ->default('Asia/Jakarta')
                        ->maxLength(64),
                    Forms\Components\Select::make('roles')
                        ->label('Peran')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->defaultImageUrl(fn (User $record): string => 'https://www.gravatar.com/avatar/' . md5(strtolower(trim((string) $record->email))) . '?d=mp&r=g&s=250')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor HP')
                    ->formatStateUsing(fn ($state): string => PhoneNormalizer::display($state))
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (UserStatus $state): string => $state->label())
                    ->color(fn (UserStatus $state): string => $state->filamentColor()),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(UserStatus::options()),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('Semua akun, transaksi, anggaran, dan target pengguna ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalDescription('Semua data keuangan milik pengguna terpilih akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Tambahkan pengguna atau biarkan mereka mendaftar melalui halaman registrasi.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
