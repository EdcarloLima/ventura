<?php

namespace App\Domain\Pricing\Strategies;

use App\Domain\Pricing\Contracts\PricingStrategyInterface;
use Carbon\Carbon;

class FixedHourlyStrategy implements PricingStrategyInterface
{
    private const HOURLY_RATE = 15.00;

    public function calculate(Carbon $entryTime, Carbon $exitTime): float
    {
        $minutes = $entryTime->diffInMinutes($exitTime, absolute: true);

        // Tolerância zero: entrou e saiu (mesmo que 1 min), paga.
        if ($minutes <= 0) {
            return self::HOURLY_RATE; 
        }

        // Regra matemática: 61 min / 60 = 1.01 -> ceil vira 2.0
        $hours = ceil($minutes / 60);

        return $hours * self::HOURLY_RATE;
    }
}