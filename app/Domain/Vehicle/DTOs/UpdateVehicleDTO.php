<?php

namespace App\Domain\Vehicle\DTOs;

class UpdateVehicleDTO
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly ?string $color = null,
        public readonly ?int $year = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'color' => $this->color,
            'year' => $this->year,
        ], fn($value) => $value !== null);
    }
}
