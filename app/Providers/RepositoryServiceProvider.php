<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Parking Domain
use App\Domain\Parking\Contracts\TicketRepositoryInterface;
use App\Domain\Parking\Contracts\ParkingSpotRepositoryInterface;
use App\Domain\Parking\Repositories\TicketRepository;
use App\Domain\Parking\Repositories\ParkingSpotRepository;

// Vehicle Domain
use App\Domain\Vehicle\Contracts\VehicleRepositoryInterface;
use App\Domain\Vehicle\Repositories\VehicleRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Parking Domain Repositories
        $this->app->bind(
            TicketRepositoryInterface::class,
            TicketRepository::class
        );

        $this->app->bind(
            ParkingSpotRepositoryInterface::class,
            ParkingSpotRepository::class
        );

        // Vehicle Domain Repositories
        $this->app->bind(
            VehicleRepositoryInterface::class,
            VehicleRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
