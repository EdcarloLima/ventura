<?php

namespace App\Domain\Parking\Repositories;

use App\Domain\Parking\Contracts\TicketRepositoryInterface;
use App\Domain\Parking\DTOs\TicketDTO;
use App\Domain\Parking\DTOs\CreateTicketDTO;
use App\Domain\Parking\DTOs\UpdateTicketDTO;
use App\Domain\Parking\DTOs\TicketWithRelationsDTO;
use App\Domain\Parking\DTOs\ParkingSpotDTO;
use App\Domain\Vehicle\DTOs\VehicleDTO;
use App\Domain\Parking\Models\Ticket;
use App\Domain\Parking\Enums\TicketStatus;

class TicketRepository implements TicketRepositoryInterface
{
    public function __construct(
        private Ticket $model
    ) {}

    public function findActiveTicketForVehicle(int $vehicleId): ?TicketDTO
    {
        $ticket = $this->model
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', [
                TicketStatus::ABERTO,
                TicketStatus::PAGAMENTO_PENDENTE,
                TicketStatus::PAGO
            ])
            ->first();

        return $ticket ? $this->toDTO($ticket) : null;
    }

    public function countActiveTickets(): int
    {
        return $this->model
            ->whereIn('status', [
                TicketStatus::ABERTO,
                TicketStatus::PAGAMENTO_PENDENTE,
                TicketStatus::PAGO
            ])
            ->count();
    }

    public function create(CreateTicketDTO $data): TicketDTO
    {
        $ticket = $this->model->create($data->toArray());
        
        return $this->toDTO($ticket);
    }

    public function findById(string $id): ?TicketDTO
    {
        $ticket = $this->model->find($id);
        
        return $ticket ? $this->toDTO($ticket) : null;
    }

    public function findByIdWithRelations(string $id): ?TicketWithRelationsDTO
    {
        $ticket = $this->model->with(['vehicle', 'parkingSpot'])->find($id);
        
        if (!$ticket) {
            return null;
        }

        return $this->toDTOWithRelations($ticket);
    }

    public function update(string $id, UpdateTicketDTO $data): bool
    {
        $ticket = $this->model->find($id);
        
        if (!$ticket) {
            return false;
        }

        return $ticket->update($data->toArray());
    }

    private function toDTO(Ticket $ticket): TicketDTO
    {
        return new TicketDTO(
            id: $ticket->id,
            vehicleId: $ticket->vehicle_id,
            spotId: $ticket->spot_id,
            entryAt: $ticket->entry_at,
            status: $ticket->status,
            gateId: $ticket->gate_id,
            exitAt: $ticket->exit_at,
            createdAt: $ticket->created_at,
            updatedAt: $ticket->updated_at
        );
    }

    private function toDTOWithRelations(Ticket $ticket): TicketWithRelationsDTO
    {
        return new TicketWithRelationsDTO(
            ticket: $this->toDTO($ticket),
            vehicle: new VehicleDTO(
                id: $ticket->vehicle->id,
                plate: $ticket->vehicle->plate,
                type: $ticket->vehicle->type,
                brand: $ticket->vehicle->brand,
                model: $ticket->vehicle->model,
                color: $ticket->vehicle->color,
                year: $ticket->vehicle->year,
                wasRecentlyCreated: $ticket->vehicle->wasRecentlyCreated,
                createdAt: $ticket->vehicle->created_at,
                updatedAt: $ticket->vehicle->updated_at
            ),
            spot: new ParkingSpotDTO(
                id: $ticket->parkingSpot->id,
                code: $ticket->parkingSpot->code,
                status: $ticket->parkingSpot->status
            )
        );
    }
}
