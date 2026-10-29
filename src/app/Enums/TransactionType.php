<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Pemasukan',
            self::Expense => 'Pengeluaran',
            self::TransferOut => 'Transfer Keluar',
            self::TransferIn => 'Transfer Masuk',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Income => 'Masuk',
            self::Expense => 'Keluar',
            self::TransferOut => 'Transfer',
            self::TransferIn => 'Transfer',
        };
    }

    public function isTransfer(): bool
    {
        return in_array($this, [self::TransferOut, self::TransferIn], true);
    }

    public function isIncome(): bool
    {
        return $this === self::Income;
    }

    public function isExpense(): bool
    {
        return $this === self::Expense;
    }

    public function sign(): int
    {
        return match ($this) {
            self::Income, self::TransferIn => 1,
            self::Expense, self::TransferOut => -1,
        };
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Income => 'success',
            self::Expense => 'danger',
            self::TransferOut => 'warning',
            self::TransferIn => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }

    public static function simpleOptions(): array
    {
        return [
            self::Income->value => self::Income->label(),
            self::Expense->value => self::Expense->label(),
        ];
    }
}
