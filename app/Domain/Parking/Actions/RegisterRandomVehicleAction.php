<?php

namespace App\Domain\Parking\Actions;

use App\Domain\Parking\DTOs\TicketWithRelationsDTO;
use App\Domain\Vehicle\Services\PlateGeneratorService;

class RegisterRandomVehicleAction
{
    public function __construct(
        private PlateGeneratorService $plateGenerator,
        private RegisterEntryAction $registerEntryAction
    ) {}

    /**
     * Registra um veículo com placa gerada aleatoriamente
     */
    public function execute(?string $gateId = null): TicketWithRelationsDTO
    {
        $plate = $this->plateGenerator->generate();
        $gateId = $gateId ?? 'entrada-1';

        return $this->registerEntryAction->execute($plate, $gateId);
    }
}
