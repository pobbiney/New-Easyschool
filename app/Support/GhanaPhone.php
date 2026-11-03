<?php

namespace App\Support;

class GhanaPhone
{
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', trim((string) $phone));

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '233') && strlen($digits) === 12) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return $digits;
        }

        if (strlen($digits) === 9) {
            return '0'.$digits;
        }

        return null;
    }
}
