<?php

namespace App\Domain\Parking\Repositories;

use App\Domain\Parking\Contracts\ParkingSpotRepositoryInterface;
use App\Domain\Parking\DTOs\ParkingSpotDTO;
use App\Domain\Parking\Models\ParkingSpot;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Domain\Parking\Enums\TicketStatus;

class ParkingSpotRepository implements ParkingSpotRepositoryInterface
{
    public function __construct(
        private ParkingSpot $model
    ) {}

    public function findAvailableSpot(): ?ParkingSpotDTO
    {
        $spot = $this->model
            ->where('status', ParkingSpotStatus::DISPONIVEL)
            ->whereDoesntHave('tickets', function ($query) {
                $query->whereIn('status', [
                    TicketStatus::ABERTO,
                    TicketStatus::PAGAMENTO_PENDENTE,
                    TicketStatus::PAGO
                ]);
            })
            ->lockForUpdate()
            ->first();

        return $spot ? $this->toDTO($spot) : null;
    }

    public function updateStatus(int $spotId, string $status): bool
    {
        $spot = $this->model->find($spotId);
        
        if (!$spot) {
            return false;
        }

        return $spot->update(['status' => $status]);
    }

    public function findById(int $id): ?ParkingSpotDTO
    {
        $spot = $this->model->find($id);
        
        return $spot ? $this->toDTO($spot) : null;
    }

    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }

    private function toDTO(ParkingSpot $spot): ParkingSpotDTO
    {
        return new ParkingSpotDTO(
            id: $spot->id,
            code: $spot->code,
            status: $spot->status,
            createdAt: $spot->created_at,
            updatedAt: $spot->updated_at
        );
    }
}
