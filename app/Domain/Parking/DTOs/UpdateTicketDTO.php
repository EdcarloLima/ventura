<?php

namespace App\Domain\Parking\DTOs;

use DateTimeInterface;

class UpdateTicketDTO
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?DateTimeInterface $exitAt = null,
        public readonly ?DateTimeInterface $paidAt = null,
        public readonly ?float $totalAmount = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'exit_at' => $this->exitAt,
            'paid_at' => $this->paidAt,
            'total_amount' => $this->totalAmount,
        ], fn($value) => $value !== null);
    }
}
