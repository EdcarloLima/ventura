<?php

namespace App\Providers;

use App\Domain\Vehicle\Contracts\VehicleLookupServiceInterface;
use App\Infrastructure\Detran\DetranApiAdapter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind da interface VehicleLookupServiceInterface para DetranApiAdapter
        $this->app->bind(
            VehicleLookupServiceInterface::class,
            DetranApiAdapter::class
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
