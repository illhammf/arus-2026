<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Posted = 'posted';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Posted => 'Tercatat',
            self::Void => 'Dibatalkan',
        };
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Posted => 'success',
            self::Void => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
