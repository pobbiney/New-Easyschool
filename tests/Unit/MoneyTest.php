<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[DataProvider('exactAmounts')]
    public function test_decimal_amounts_convert_to_exact_minor_units(string $decimal, int $minor): void
    {
        $this->assertSame($minor, Money::toMinor($decimal));
        $this->assertSame(number_format($minor / 100, 2, '.', ''), Money::fromMinor($minor));
    }

    public static function exactAmounts(): array
    {
        return [
            ['0', 0],
            ['0.01', 1],
            ['0.10', 10],
            ['10.09', 1009],
            ['9999999999.99', 999999999999],
        ];
    }

    #[DataProvider('invalidAmounts')]
    public function test_invalid_precision_and_boundaries_are_rejected(string $amount): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::toMinor($amount);
    }

    public static function invalidAmounts(): array
    {
        return [['-0.01'], ['0.001'], ['1e2'], ['10000000000.00'], ['not-money']];
    }
}
