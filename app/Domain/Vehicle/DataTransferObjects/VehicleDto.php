<?php

namespace App\Domain\Vehicle\DataTransferObjects;

/**
 * Este objeto transporta dados entre a Infraestrutura (API Detran) e o Domínio.
 * Usamos 'readonly' para garantir imutabilidade.
 */
readonly class VehicleDto
{
    public function __construct(
        public string $plate,
        public string $brand,    // Ex: Volkswagen
        public string $model,    // Ex: Gol 1.0
        public string $color,    // Ex: Branco
        public string $category  // Ex: CARRO
    ) {}

    /**
     * Método estático para criar a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            plate: $data['plate'],
            brand: $data['brand'],
            model: $data['model'],
            color: $data['color'],
            category: $data['category'] ?? 'CARRO'
        );
    }
}