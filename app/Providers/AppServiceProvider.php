<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Infrastructure\Detran\DetranApiAdapter;
use App\Domain\Pricing\Strategies\FixedHourlyStrategy;
use App\Infrastructure\MercadoPago\MercadoPagoGateway;
use App\Domain\Payment\Contracts\PaymentGatewayInterface;
use App\Domain\Pricing\Contracts\PricingStrategyInterface;
use App\Domain\Vehicle\Contracts\VehicleLookupServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            VehicleLookupServiceInterface::class,
            DetranApiAdapter::class
        );

        $this->app->bind(
            PricingStrategyInterface::class, 
            FixedHourlyStrategy::class
        );

        $this->app->bind(
            PaymentGatewayInterface::class,
            MercadoPagoGateway::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
