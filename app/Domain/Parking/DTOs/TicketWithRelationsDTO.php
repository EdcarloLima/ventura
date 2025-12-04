<?php

namespace App\Domain\Parking\DTOs;

use App\Domain\Vehicle\DTOs\VehicleDTO;

class TicketWithRelationsDTO
{
    public function __construct(
        public readonly TicketDTO $ticket,
        public readonly VehicleDTO $vehicle,
        public readonly ParkingSpotDTO $spot
    ) {}
}
