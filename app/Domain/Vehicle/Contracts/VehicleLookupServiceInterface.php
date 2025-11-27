<?php

namespace App\Domain\Vehicle\Contracts;

use App\Domain\Vehicle\DataTransferObjects\VehicleDto;

interface VehicleLookupServiceInterface
{
    /**
     * Busca os dados de um veículo pela placa.
     * * @param string $plate A placa do veículo (ex: ABC1234)
     * @return VehicleDto|null Retorna o DTO com os dados ou null se não encontrar/falhar.
     */
    public function findByPlate(string $plate): ?VehicleDto;
}