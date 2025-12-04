<?php

namespace App\Jobs;

use App\Domain\Vehicle\Contracts\VehicleLookupServiceInterface;
use App\Domain\Vehicle\Contracts\VehicleRepositoryInterface;
use App\Domain\Vehicle\DTOs\UpdateVehicleDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchVehicleDetailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue; 
    use Queueable; 
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $vehicleId
    ) {}

    public function handle(
        VehicleLookupServiceInterface $detranService,
        VehicleRepositoryInterface $vehicleRepository
    ): void
    {
        $vehicle = $vehicleRepository->findById($this->vehicleId);

        if (!$vehicle) {
            Log::warning("Veículo não encontrado.", [
                'vehicle_id' => $this->vehicleId
            ]);
            return;
        }

        Log::info("Buscando detalhes para a placa.", [
            'placa' => $vehicle->plate
        ]);

        try {
            $vehicleDto = $detranService->findByPlate($vehicle->plate);

            if (!$vehicleDto) {
                Log::warning("Veículo não encontrado no Detran ou erro na API externa.", [
                    'placa' => $vehicle->plate
                ]);
                
                return;
            }

            $updateDTO = new UpdateVehicleDTO(
                type: $vehicleDto->category,
                brand: $vehicleDto->brand,
                model: $vehicleDto->model,
                color: $vehicleDto->color
            );
            
            $vehicleRepository->update($vehicle->id, $updateDTO);

            Log::info("Veículo atualizado!", [
                'placa' => $vehicle->plate,
                'brand' => $vehicleDto->brand,
                'model' => $vehicleDto->model,
                'color' => $vehicleDto->color,
                'type'  => $vehicleDto->category,
            ]);

        } catch (\Throwable $e) {
            Log::critical("Falha ao processar placa.", [
                'placa' => $vehicle->plate,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            throw $e; 
        }
    }
}
