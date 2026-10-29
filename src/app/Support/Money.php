<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public const SCALE = 2;

    public static function normalize(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Nilai uang tidak valid.');
        }

        return bcadd((string) $value, '0', self::SCALE);
    }

    public static function add(mixed ...$values): string
    {
        $total = '0.00';

        foreach ($values as $value) {
            $total = bcadd($total, self::normalize($value), self::SCALE);
        }

        return $total;
    }

    public static function sub(mixed $left, mixed $right): string
    {
        return bcsub(self::normalize($left), self::normalize($right), self::SCALE);
    }

    public static function compare(mixed $left, mixed $right): int
    {
        return bccomp(self::normalize($left), self::normalize($right), self::SCALE);
    }

    public static function isPositive(mixed $value): bool
    {
        return self::compare($value, '0.00') > 0;
    }

    public static function format(mixed $amount, string $currency = 'IDR'): string
    {
        $value = self::normalize($amount);
        $negative = str_starts_with($value, '-');
        $absolute = ltrim($value, '-');

        if (strtoupper($currency) === 'IDR') {
            $formatted = 'Rp' . number_format((float) $absolute, 0, ',', '.');
        } else {
            $formatted = strtoupper($currency) . ' ' . number_format((float) $absolute, 2, ',', '.');
        }

        return ($negative ? '-' : '') . $formatted;
    }

    public static function input(mixed $amount): string
    {
        return self::normalize($amount);
    }
}
