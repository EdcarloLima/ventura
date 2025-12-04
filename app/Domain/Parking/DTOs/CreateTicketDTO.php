<?php

namespace App\Domain\Parking\DTOs;

use DateTimeInterface;

class CreateTicketDTO
{
    public function __construct(
        public readonly int $vehicleId,
        public readonly int $spotId,
        public readonly DateTimeInterface $entryAt,
        public readonly string $status,
        public readonly string $gateId
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vehicleId: $data['vehicle_id'],
            spotId: $data['spot_id'],
            entryAt: $data['entry_at'],
            status: $data['status'],
            gateId: $data['gate_id']
        );
    }

    public function toArray(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'spot_id' => $this->spotId,
            'entry_at' => $this->entryAt,
            'status' => $this->status,
            'gate_id' => $this->gateId,
        ];
    }
}
