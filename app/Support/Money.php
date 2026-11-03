<?php

namespace App\Support;

class Money
{
    public static function ghs($amount): string
    {
        return 'GHS '.number_format((float) $amount, 2);
    }
}
