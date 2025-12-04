<?php

namespace App\Domain\Vehicle\DTOs;

class CreateVehicleDTO
{
    public function __construct(
        public readonly string $plate,
        public readonly ?string $type = null,
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly ?string $color = null,
        public readonly ?int $year = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plate: $data['plate'],
            type: $data['type'] ?? null,
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
            color: $data['color'] ?? null,
            year: $data['year'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'plate' => $this->plate,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'color' => $this->color,
            'year' => $this->year,
        ], fn($value) => $value !== null);
    }
}
