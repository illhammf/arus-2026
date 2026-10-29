<?php

use App\Support\Money;

it('normalizes values to two decimal places without floating point arithmetic', function () {
    expect(Money::normalize('1234.5'))->toBe('1234.50')
        ->and(Money::normalize(10))->toBe('10.00')
        ->and(Money::normalize(null))->toBe('0.00')
        ->and(Money::normalize(''))->toBe('0.00');
});

it('adds and subtracts money exactly', function () {
    expect(Money::add('0.10', '0.20'))->toBe('0.30')
        ->and(Money::add('999999999999.99', '0.01'))->toBe('1000000000000.00')
        ->and(Money::sub('0.30', '0.10'))->toBe('0.20')
        ->and(Money::sub('10.00', '12.50'))->toBe('-2.50');
});

it('compares and checks positive amounts', function () {
    expect(Money::compare('10.00', '10.00'))->toBe(0)
        ->and(Money::compare('10.01', '10.00'))->toBe(1)
        ->and(Money::compare('9.99', '10.00'))->toBe(-1)
        ->and(Money::isPositive('0.01'))->toBeTrue()
        ->and(Money::isPositive('0.00'))->toBeFalse()
        ->and(Money::isPositive('-1.00'))->toBeFalse();
});

it('formats rupiah and other currencies', function () {
    expect(Money::format('1234567.89'))->toBe('Rp1.234.568')
        ->and(Money::format('-2500000'))->toBe('-Rp2.500.000')
        ->and(Money::format('10.5', 'USD'))->toBe('USD 10,50');
});

it('rejects non numeric values', function () {
    Money::normalize('not-a-number');
})->throws(InvalidArgumentException::class);
