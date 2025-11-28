<?php

namespace App\Domain\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PaymentConfirmed
{
    use Dispatchable;

    public function __construct(
        public string $transactionId,
        public float $amount
    ) {}
}