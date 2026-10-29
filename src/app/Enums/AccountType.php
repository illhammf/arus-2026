<?php

namespace App\Enums;

enum AccountType: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case EWallet = 'ewallet';
    case Savings = 'savings';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Bank => 'Rekening Bank',
            self::EWallet => 'E-Wallet',
            self::Savings => 'Tabungan',
            self::Other => 'Lainnya',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'banknotes',
            self::Bank => 'building-library',
            self::EWallet => 'device-phone-mobile',
            self::Savings => 'archive-box',
            self::Other => 'wallet',
        };
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Cash => 'success',
            self::Bank => 'info',
            self::EWallet => 'warning',
            self::Savings => 'primary',
            self::Other => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
