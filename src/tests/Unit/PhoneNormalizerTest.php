<?php

use App\Support\PhoneNormalizer;

it('normalizes indonesian phone numbers to e164', function (string $input, string $expected) {
    expect(PhoneNormalizer::normalize($input))->toBe($expected);
})->with([
    ['081234567890', '+6281234567890'],
    ['0812-3456-7890', '+6281234567890'],
    ['+62 812 3456 7890', '+6281234567890'],
    ['6281234567890', '+6281234567890'],
    ['81234567890', '+6281234567890'],
    ['(0812) 3456 7890', '+6281234567890'],
]);

it('rejects invalid phone numbers', function (string $input) {
    expect(PhoneNormalizer::normalize($input))->toBeNull();
})->with([
    '',
    '12345',
    '021234567',
    '0812',
    'abcdefghij',
    '00123456789',
]);

it('displays phone numbers in local format', function () {
    expect(PhoneNormalizer::display('+6281234567890'))->toBe('081234567890')
        ->and(PhoneNormalizer::display(null))->toBe('-')
        ->and(PhoneNormalizer::display(''))->toBe('-');
});
