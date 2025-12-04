<?php

namespace App\Domain\Vehicle\Repositories;

use App\Domain\Vehicle\Contracts\VehicleRepositoryInterface;
use App\Domain\Vehicle\DTOs\VehicleDTO;
use App\Domain\Vehicle\DTOs\CreateVehicleDTO;
use App\Domain\Vehicle\DTOs\UpdateVehicleDTO;
use App\Domain\Vehicle\Models\Vehicle;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function __construct(
        private Vehicle $model
    ) {}

    public function findByPlate(string $plate): ?VehicleDTO
    {
        $vehicle = $this->model->where('plate', $plate)->first();
        
        return $vehicle ? $this->toDTO($vehicle) : null;
    }

    public function firstOrCreate(CreateVehicleDTO $data): VehicleDTO
    {
        $vehicle = $this->model->firstOrCreate(
            ['plate' => $data->plate],
            $data->toArray()
        );
        
        return $this->toDTO($vehicle, $vehicle->wasRecentlyCreated);
    }

    public function update(int $vehicleId, UpdateVehicleDTO $data): bool
    {
        $vehicle = $this->model->find($vehicleId);
        
        if (!$vehicle) {
            return false;
        }

        return $vehicle->update($data->toArray());
    }

    public function findById(int $id): ?VehicleDTO
    {
        $vehicle = $this->model->find($id);
        
        return $vehicle ? $this->toDTO($vehicle) : null;
    }

    private function toDTO(Vehicle $vehicle, bool $wasRecentlyCreated = false): VehicleDTO
    {
        return new VehicleDTO(
            id: $vehicle->id,
            plate: $vehicle->plate,
            type: $vehicle->type,
            brand: $vehicle->brand,
            model: $vehicle->model,
            color: $vehicle->color,
            year: $vehicle->year,
            wasRecentlyCreated: $wasRecentlyCreated,
            createdAt: $vehicle->created_at,
            updatedAt: $vehicle->updated_at
        );
    }
}
