<?php

namespace App\Domain\Parking\Contracts;

use App\Domain\Parking\DTOs\ParkingSpotDTO;

interface ParkingSpotRepositoryInterface
{
    /**
     * Find an available parking spot.
     *
     * @return ParkingSpotDTO|null
     */
    public function findAvailableSpot(): ?ParkingSpotDTO;

    /**
     * Update parking spot status.
     *
     * @param int $spotId
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $spotId, string $status): bool;

    /**
     * Find spot by ID.
     *
     * @param int $id
     * @return ParkingSpotDTO|null
     */
    public function findById(int $id): ?ParkingSpotDTO;

    /**
     * Count spots by status.
     *
     * @param string $status
     * @return int
     */
    public function countByStatus(string $status): int;
}
