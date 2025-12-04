<?php

namespace App\Domain\Vehicle\DTOs;

use DateTimeInterface;

class VehicleDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $plate,
        public readonly ?string $type = null,
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly ?string $color = null,
        public readonly ?int $year = null,
        public readonly bool $wasRecentlyCreated = false,
        public readonly ?DateTimeInterface $createdAt = null,
        public readonly ?DateTimeInterface $updatedAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            plate: $data['plate'],
            type: $data['type'] ?? null,
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
            color: $data['color'] ?? null,
            year: $data['year'] ?? null,
            wasRecentlyCreated: $data['was_recently_created'] ?? false,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'plate' => $this->plate,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'color' => $this->color,
            'year' => $this->year,
            'was_recently_created' => $this->wasRecentlyCreated,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
