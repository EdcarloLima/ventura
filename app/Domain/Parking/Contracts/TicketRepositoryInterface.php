<?php

namespace App\Domain\Parking\Contracts;

use App\Domain\Parking\DTOs\TicketDTO;
use App\Domain\Parking\DTOs\CreateTicketDTO;
use App\Domain\Parking\DTOs\UpdateTicketDTO;
use App\Domain\Parking\DTOs\TicketWithRelationsDTO;

interface TicketRepositoryInterface
{
    /**
     * Find an active ticket for a vehicle.
     *
     * @param int $vehicleId
     * @return TicketDTO|null
     */
    public function findActiveTicketForVehicle(int $vehicleId): ?TicketDTO;

    /**
     * Count active tickets.
     *
     * @return int
     */
    public function countActiveTickets(): int;

    /**
     * Create a new ticket.
     *
     * @param CreateTicketDTO $data
     * @return TicketDTO
     */
    public function create(CreateTicketDTO $data): TicketDTO;

    /**
     * Find ticket by ID.
     *
     * @param string $id
     * @return TicketDTO|null
     */
    public function findById(string $id): ?TicketDTO;

    /**
     * Find ticket by ID with relations.
     *
     * @param string $id
     * @return TicketWithRelationsDTO|null
     */
    public function findByIdWithRelations(string $id): ?TicketWithRelationsDTO;

    /**
     * Update ticket.
     *
     * @param string $id
     * @param UpdateTicketDTO $data
     * @return bool
     */
    public function update(string $id, UpdateTicketDTO $data): bool;
}
