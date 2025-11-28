<?php

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\DataTransferObjects\PaymentRegisterDto;

interface PaymentGatewayInterface
{
    public function createPixPayment(float $amount, string $description, string $payerEmail): PaymentRegisterDto;
}