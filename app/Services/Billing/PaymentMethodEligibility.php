<?php

namespace App\Services\Billing;

use App\Models\BillingItem;
use InvalidArgumentException;

class PaymentMethodEligibility
{
    public const FEEDING_FEE_METHOD_MESSAGE = 'Feeding Fee payments can only be made through cash or approved bank payment methods.';

    /** @var list<string> */
    private const FEEDING_FEE_METHODS = ['Cash', 'Bank'];

    public function assertAllowed(string $financialCategory, string $paymentMethod, bool $historicalGatewayCompletion = false): void
    {
        if ($financialCategory !== BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
            return;
        }

        if ($historicalGatewayCompletion && $paymentMethod === 'Paystack') {
            return;
        }

        if (! in_array($paymentMethod, self::FEEDING_FEE_METHODS, true)) {
            throw new InvalidArgumentException(self::FEEDING_FEE_METHOD_MESSAGE);
        }
    }

    public function assertPaystackInitializationAllowed(string $financialCategory): void
    {
        $this->assertAllowed($financialCategory, 'Paystack');
    }
}
