<?php

namespace App\Console\Commands;

use App\Services\Billing\DevelopmentPaymentCorrectionService;
use Illuminate\Console\Command;

class VoidDevelopmentBillPayment extends Command
{
    protected $signature = 'billing:void-development-payment
        {receipt : Exact receipt number}
        {--expected-amount= : Exact expected amount}
        {--reason= : Audit reason}
        {--actor= : Responsible maintenance actor}
        {--actor-user-id= : Optional local user ID}';

    protected $description = 'Void one verified, unposted development bill payment in a local environment';

    public function handle(DevelopmentPaymentCorrectionService $service): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('This controlled correction command is restricted to local and testing environments.');

            return self::FAILURE;
        }

        $amount = (string) $this->option('expected-amount');
        $reason = (string) $this->option('reason');
        $actor = (string) $this->option('actor');

        if ($amount === '' || $reason === '' || $actor === '') {
            $this->error('--expected-amount, --reason and --actor are required.');

            return self::INVALID;
        }

        $payment = $service->voidUnpostedPayment(
            (string) $this->argument('receipt'),
            $amount,
            $reason,
            $actor,
            $this->option('actor-user-id') !== null ? (int) $this->option('actor-user-id') : null,
        );

        $this->info("Receipt {$payment->receipt_no} is {$payment->status}; its original payment and allocation history remain intact.");

        return self::SUCCESS;
    }
}
