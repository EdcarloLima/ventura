<?php

namespace App\Domain\Pricing\Contracts;

use Carbon\Carbon;

interface PricingStrategyInterface
{
    public function calculate(Carbon $entryTime, Carbon $exitTime): float;
}