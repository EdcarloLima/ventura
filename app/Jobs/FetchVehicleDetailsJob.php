<?php

namespace App\Jobs;

use App\Domain\Vehicle\Contracts\VehicleLookupServiceInterface;
use App\Domain\Vehicle\Models\Vehicle;
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

    /**
     * Número de tentativas em caso de falha.
     * Se o Detran cair, o Laravel tenta de novo 3 vezes.
     */
    public int $tries = 3;

    /**
     * Tempo de espera (em segundos) entre as tentativas.
     */
    public int $backoff = 10;

    /**
     * O Job recebe o veículo que precisa ser atualizado.
     */
    public function __construct(
        public readonly Vehicle $vehicle
    ) {}

    /**
     * Injeta automaticamente a implementação do Detran (DetranApiAdapter).
     */
    public function handle(VehicleLookupServiceInterface $detranService): void
    {
        Log::info("Buscando detalhes para a placa.", [
            'placa' => $this->vehicle->plate
        ]);

        try {
            $vehicleDto = $detranService->findByPlate($this->vehicle->plate);

            if (!$vehicleDto) {
                Log::warning("Veículo não encontrado no Detran ou erro na API externa.", [
                    'placa' => $this->vehicle->plate
                ]);
                
                return;
            }

            $this->vehicle->update([
                'brand' => $vehicleDto->brand,
                'model' => $vehicleDto->model,
                'color' => $vehicleDto->color,
                'type'  => $vehicleDto->category,
            ]);

            Log::info("Veículo atualizado!", [
                'placa' => $this->vehicle->plate,
                'brand' => $vehicleDto->brand,
                'model' => $vehicleDto->model,
                'color' => $vehicleDto->color,
                'type'  => $vehicleDto->category,
            ]);

        } catch (\Throwable $e) {
            Log::critical("Falha ao processar placa.", [
                'placa' => $this->vehicle->plate,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            throw $e; 
        }
    }
}
