<?php

namespace App\Domain\Parking\Actions;

use App\Domain\Parking\Contracts\TicketRepositoryInterface;
use App\Domain\Parking\Contracts\ParkingSpotRepositoryInterface;
use App\Domain\Vehicle\Contracts\VehicleRepositoryInterface;
use App\Domain\Parking\DTOs\TicketWithRelationsDTO;
use App\Domain\Parking\DTOs\CreateTicketDTO;
use App\Domain\Vehicle\DTOs\CreateVehicleDTO;
use App\Domain\Parking\Enums\TicketStatus;
use App\Domain\Parking\Enums\ParkingSpotStatus;
use App\Jobs\FetchVehicleDetailsJob;
use Illuminate\Support\Facades\DB;
use Exception;

class RegisterEntryAction
{
    // TO-DO: Move to config
    private const MAX_CAPACITY = 100;

    public function __construct(
        private TicketRepositoryInterface $ticketRepository,
        private VehicleRepositoryInterface $vehicleRepository,
        private ParkingSpotRepositoryInterface $spotRepository
    ) {}

    public function execute(string $plate, string $gateId): TicketWithRelationsDTO
    {
        $shouldFetchVehicleDetails = false;
        $vehicleId = null;

        $result = DB::transaction(function () use ($plate, $gateId, &$shouldFetchVehicleDetails, &$vehicleId) {
            
            $vehicle = $this->vehicleRepository->findByPlate($plate);

            if ($vehicle) {
                $activeTicket = $this->ticketRepository->findActiveTicketForVehicle($vehicle->id);

                if ($activeTicket) {
                    throw new Exception("Veículo placa {$plate} já está no pátio.", 409);
                }
            }

            $currentCount = $this->ticketRepository->countActiveTickets();

            if ($currentCount >= self::MAX_CAPACITY) {
                throw new Exception("Estacionamento lotado.", 422);
            }

            $vehicleDTO = new CreateVehicleDTO(plate: $plate);
            $vehicle = $this->vehicleRepository->firstOrCreate($vehicleDTO);

            if ($vehicle->wasRecentlyCreated || is_null($vehicle->model)) {
                $shouldFetchVehicleDetails = true;
                $vehicleId = $vehicle->id;
            }

            $parkingSpot = $this->spotRepository->findAvailableSpot();

            if (!$parkingSpot) {
                throw new Exception("Nenhuma vaga disponível no momento.", 422);
            }

            $this->spotRepository->updateStatus($parkingSpot->id, ParkingSpotStatus::OCUPADO);

            $createTicketDTO = new CreateTicketDTO(
                vehicleId: $vehicle->id,
                spotId: $parkingSpot->id,
                entryAt: now(),
                status: TicketStatus::ABERTO,
                gateId: $gateId
            );
            
            $ticket = $this->ticketRepository->create($createTicketDTO);

            return $this->ticketRepository->findByIdWithRelations($ticket->id);
        });

        if ($shouldFetchVehicleDetails && $vehicleId) {
            FetchVehicleDetailsJob::dispatch($vehicleId);
        }

        return $result;
    }
}