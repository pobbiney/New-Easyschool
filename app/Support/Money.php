<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    public static function ghs($amount): string
    {
        return 'GHS '.number_format((float) $amount, 2);
    }

    /** Convert a non-negative decimal amount to exact pesewas. */
    public static function toMinor(string|int|float $amount, int $maximumMinor = 999999999999): int
    {
        $value = trim((string) $amount);

        if (! preg_match('/^(0|[1-9]\d*)(?:\.(\d{1,2}))?$/', $value, $matches)) {
            throw new InvalidArgumentException('Money must be a non-negative amount with at most two decimal places.');
        }

        $whole = $matches[1];
        $fraction = str_pad($matches[2] ?? '', 2, '0');

        if (strlen($whole) > 10) {
            throw new InvalidArgumentException('Money exceeds the supported maximum.');
        }

        $minor = ((int) $whole * 100) + (int) $fraction;

        if ($minor > $maximumMinor) {
            throw new InvalidArgumentException('Money exceeds the supported maximum.');
        }

        return $minor;
    }

    public static function fromMinor(int $minor): string
    {
        if ($minor < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }

        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
