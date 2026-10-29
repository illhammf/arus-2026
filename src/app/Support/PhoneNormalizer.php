<?php

namespace App\Support;

final class PhoneNormalizer
{
    /**
     * Normalize Indonesian phone numbers to E.164 (+62...).
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        if (! preg_match('/^628[1-9][0-9]{6,11}$/', $digits)) {
            return null;
        }

        return '+' . $digits;
    }

    public static function isValid(?string $phone): bool
    {
        return self::normalize($phone) !== null;
    }

    public static function display(?string $phone): string
    {
        if (! $phone) {
            return '-';
        }

        if (str_starts_with($phone, '+62')) {
            return '0' . substr($phone, 3);
        }

        return $phone;
    }
}
