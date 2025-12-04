<?php

namespace App\Domain\Parking\DTOs;

use DateTimeInterface;

class TicketDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $vehicleId,
        public readonly int $spotId,
        public readonly DateTimeInterface $entryAt,
        public readonly string $status,
        public readonly ?string $gateId = null,
        public readonly ?DateTimeInterface $exitAt = null,
        public readonly ?DateTimeInterface $createdAt = null,
        public readonly ?DateTimeInterface $updatedAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            vehicleId: $data['vehicle_id'],
            spotId: $data['spot_id'],
            entryAt: $data['entry_at'],
            status: $data['status'],
            gateId: $data['gate_id'],
            exitAt: $data['exit_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicleId,
            'spot_id' => $this->spotId,
            'entry_at' => $this->entryAt,
            'status' => $this->status,
            'gate_id' => $this->gateId,
            'exit_at' => $this->exitAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
