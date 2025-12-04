<?php

namespace App\Domain\Vehicle\Contracts;

use App\Domain\Vehicle\DTOs\VehicleDTO;
use App\Domain\Vehicle\DTOs\CreateVehicleDTO;
use App\Domain\Vehicle\DTOs\UpdateVehicleDTO;

interface VehicleRepositoryInterface
{
    /**
     * Find vehicle by plate.
     *
     * @param string $plate
     * @return VehicleDTO|null
     */
    public function findByPlate(string $plate): ?VehicleDTO;

    /**
     * Create or update vehicle.
     *
     * @param CreateVehicleDTO $data
     * @return VehicleDTO
     */
    public function firstOrCreate(CreateVehicleDTO $data): VehicleDTO;

    /**
     * Update vehicle details.
     *
     * @param int $vehicleId
     * @param UpdateVehicleDTO $data
     * @return bool
     */
    public function update(int $vehicleId, UpdateVehicleDTO $data): bool;

    /**
     * Find vehicle by ID.
     *
     * @param int $id
     * @return VehicleDTO|null
     */
    public function findById(int $id): ?VehicleDTO;
}
